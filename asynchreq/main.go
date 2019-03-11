package main

import (
	"bytes"
	"encoding/json"
	"fmt"
	"io/ioutil"
	"net"
	"net/http"
	"os"
	"regexp"
	"time"

	"github.com/gorilla/mux"
	"go.uber.org/zap"
)

// пример запроса
// http://localhost:8181/api/request/create?host=http://user.ru&url=/api/user/show&data={"user_id":1}
// путь к файлу логов
const logPath = "example.log"

//структура конфига
type Config struct {
	Host            string
	ChanLen         int
	TrustedServices []string
}

var config Config

// тип асинхронного запроса
// 	{
// 		Host: "http://user.ru:8181",
// 		Url:  "/api/user/delete",
// 		Data: "[id:10, name:"user"]"
// 	}
type AsynchRequest struct {
	Host string
	Url  string
	Data string
}

// пул очередей асинхронных запросов
// 0xc0000f41e0 - адрес очереди
// 	[
// 		"http://user.ru:8181":"0xc0000f41e0",
// 		"http://avito.ru":"0xc0000f8956"
//      ...
// 	]
type PullChanAR map[string]chan AsynchRequest

// пул асинхронных запросов
// 	[
// 		{Host:"http://user.ru:8181", Url:"/api/user/delete", Data:"[id:10, name:"user"]"},
// 		{Host:"http://user.ru:8181", Url:"/api/user/create", Data:"[id:10]"}
// 		...
// 	]
type PullAR []AsynchRequest

var pullChanAR PullChanAR = PullChanAR{}

func requestCreate(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json;charset=utf-8")
	//проверить от кого запрос
	flagTrustIP := false
	ip, _, err := net.SplitHostPort(r.RemoteAddr)
	if err == nil {
		for _, trip := range config.TrustedServices {
			if trip == ip {
				flagTrustIP = true
				break
			}
		}
	}
	// выводим ошибку о том что сервис не доверенный
	if flagTrustIP == false {
		js := json.RawMessage(`{"status":"except", "data":{"massege":"Недоверенный сервис."}}`)
		w.Write(js)
		return
	}
	host := r.URL.Query().Get("host")
	if host == "" {
		host = r.FormValue("host")
	}
	if host == "" {
		js := json.RawMessage(`{"status":"except", "data":{"massege":"Ошибки в запросе.","host":"Не указан."}}`)
		w.Write(js)
		return
	}
	url := r.URL.Query().Get("url")
	if host == "" {
		url = r.FormValue("url")
	}
	if url == "" {
		js := json.RawMessage(`{"status":"except", "data":{"massege":"Ошибки в запросе.","url":"Не указан."}}`)
		w.Write(js)
		return
	}
	data := r.URL.Query().Get("data")
	if host == "" {
		data = r.FormValue("data")
	}

	// создать запрос
	asynchRequest := AsynchRequest{
		Host: host,
		Url:  url,
		Data: data,
	}

	// проверить существование очереди
	if _, ok := pullChanAR[asynchRequest.Host]; ok {
		// если есть такая очередь
		// проверить переполнение
		// если переполнен не добавлять запрос в пул
		if len(pullChanAR[asynchRequest.Host]) < config.ChanLen {
			pullChanAR[asynchRequest.Host] <- asynchRequest
		}

	} else {

		// создать пул очередь объемом в chanLen запросов
		var _chanAR = make(chan AsynchRequest, config.ChanLen)
		pullChanAR[asynchRequest.Host] = _chanAR
		// создать горутину
		go TransleterAR(asynchRequest.Host, pullChanAR[asynchRequest.Host])
		pullChanAR[asynchRequest.Host] <- asynchRequest
	}

	js := json.RawMessage(`{"status":"ok", "data":null}`)
	w.Write(js)
	return

}

func TransleterAR(name string, chanAR chan AsynchRequest) {
	// включаем логирование
	fmt.Println()
	fmt.Println("	Создана очередь для микросервиса:", name)
	cfg := zap.NewProductionConfig()
	t := time.Now().Format("2006-01-02")
	reg := regexp.MustCompile(`\W`)
	replaceStr := reg.ReplaceAllString(name, "")
	os.MkdirAll("./logs/"+replaceStr, os.ModePerm)
	path := "./logs/" + replaceStr + "/" + t + ".log"
	cfg.OutputPaths = []string{path}
	logger, _ := cfg.Build()
	sugar := logger.Sugar()
	fmt.Println("	Создан лог файл:", path)
	defer logger.Sync()
	var pullAR = PullAR{}
	for {
		// если пул очереди запросов не пустой
		// копируем запросы в очередь
		if len(chanAR) >= 1 {
			for i := 0; i < len(chanAR); i++ {
				pullAR = append(pullAR, <-chanAR)
			}
		}

		// если есть запросы обрабатываем их
		if len(pullAR) >= 1 {
			asReq := pullAR[0]
			data := []byte(asReq.Data)
			r := bytes.NewReader(data)
			resp, err := http.Post(fmt.Sprint(asReq.Host, asReq.Url), "application/json", r)
			if err != nil {
				sugar.Info("T: ", time.Now().Format(time.RFC3339),
					" MSG: ", "Выполнен запрос с ошибкой",
					" ERR: ", err.Error(),
				)

			} else {
				// bs := make([]byte, 1014)
				// n, _ := resp.Body.Read(bs)
				// fmt.Println(string(bs[:n]))
				// fmt.Println("Выполнил запрос: ", asReq.Host, asReq.Url)
				resp.Body.Close()
			}
			pullAR = pullAR[1:]
		}
	}
}

func main() {

	// открываем и читаем конфиг
	jsonFile, err := os.Open("config.json")
	if err != nil {
		fmt.Println(err)
	}

	byteValue, _ := ioutil.ReadAll(jsonFile)
	json.Unmarshal(byteValue, &config)
	fmt.Println("Открыт и прочитан... config.json")
	jsonFile.Close()

	// запустить сервер
	router := mux.NewRouter()
	router.HandleFunc("/api/request/create", requestCreate)
	http.Handle("/", router)
	fmt.Println("Сервер запущен и слушает ", config.Host, "/api/request/create")

	http.ListenAndServe(config.Host, nil)

}
