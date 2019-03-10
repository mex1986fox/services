package main

import (
	"bytes"
	"encoding/json"
	"fmt"
	"net/http"

	"github.com/gorilla/mux"
)

// тип асинхронного запроса
type AsynchRequest struct {
	Host string
	Url  string
	Data string
}

// // пул асинхронных запросов
// type PullAsynchRequest []AsynchRequest

// пул каналов очередей
// type PullTurn map[string]chan AsynchRequest
type PullChanAR map[string]chan AsynchRequest

var pullChanAR PullChanAR = PullChanAR{}

//для блокировке потоков при записи в PullAR
//var PullARMutex = &sync.RWMutex{}

func requestCreate(w http.ResponseWriter, r *http.Request) {

	host := r.URL.Query().Get("host")
	url := r.URL.Query().Get("url")
	data := r.URL.Query().Get("data")
	// создать запрос
	asynchRequest := AsynchRequest{
		Host: host,
		Url:  url,
		Data: data,
	}

	// проверить существование очереди
	if _, ok := pullChanAR[asynchRequest.Host]; ok {
		//если есть такая очередь
		fmt.Println("\r\t...добавил новый запрос в существующую горутину..\r\t")
		pullChanAR[asynchRequest.Host] <- asynchRequest

	} else {
		//если нет
		// создать пул очередь запросов
		var _chanAR = make(chan AsynchRequest, 5)
		pullChanAR[asynchRequest.Host] = _chanAR
		// создать горутину
		// которая будет обрабатывать очередь
		go TransleterAR(pullChanAR[asynchRequest.Host])
		pullChanAR[asynchRequest.Host] <- asynchRequest
		fmt.Println("\r\t..............создал новую горутину .............\r\t")
	}

	// //vars := mux.Vars(r)
	// ip := r.URL.Query().Get("ip")
	fmt.Println(pullChanAR)
	b, _ := json.Marshal(pullChanAR)
	response := fmt.Sprint(string(b))
	fmt.Fprint(w, response)

}

var count int = 0

func TransleterAR(chanAR chan AsynchRequest) {
	var pullAR = []AsynchRequest{}
	for {
		// если пул очереди запросов не пустой
		// копируем запросы в очередь
		if len(chanAR) >= 1 {
			fmt.Println("\r\t..не пуста.....\r\t")

			for i := 0; i < len(chanAR); i++ {
				//fmt.Println(cAR)
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
				fmt.Println("Выполнил запрос с ошибкой: ", err)

			} else {
				bs := make([]byte, 1014)
				n, _ := resp.Body.Read(bs)
				fmt.Println(string(bs[:n]))
				fmt.Println("Выполнил запрос: ", asReq.Host, asReq.Url)
				resp.Body.Close()
			}
			pullAR = pullAR[1:]
		}
	}
}

func main() {
	// запустить горутину
	// которая будет запросы отправлять
	fmt.Println("горутина стартанула")
	router := mux.NewRouter()
	router.HandleFunc("/api/request/create", requestCreate)
	http.Handle("/", router)
	fmt.Println("Server is listening...")

	http.ListenAndServe(":8181", nil)

}
