package main

import (
	"bytes"
	"encoding/json"
	"fmt"
	"net/http"

	"github.com/gorilla/mux"
)

type AsynchRequest struct {
	Host string
	Port string
	Url  string
	Data string
}

var PullAR []AsynchRequest = []AsynchRequest{}

func requestCreate(w http.ResponseWriter, r *http.Request) {

	host := r.URL.Query().Get("host")
	port := r.URL.Query().Get("port")
	url := r.URL.Query().Get("url")
	data := r.URL.Query().Get("data")
	// создать массив запросов
	asynchRequest := AsynchRequest{
		Host: host,
		Port: port,
		Url:  url,
		Data: data,
	}
	PullAR = append(PullAR, asynchRequest)
	// //vars := mux.Vars(r)
	// ip := r.URL.Query().Get("ip")
	b, _ := json.Marshal(PullAR)
	response := fmt.Sprint(string(b))
	fmt.Fprint(w, response)

}

func TransleterAR() {
	for {
		if len(PullAR) >= 1 {
			asReq := PullAR[0]
			data := []byte(asReq.Data)
			r := bytes.NewReader(data)
			resp, err := http.Post(fmt.Sprint("http://", asReq.Host, ":", asReq.Port, asReq.Url), "application/json", r)
			if err != nil {
				fmt.Println(err)

			} else {
				bs := make([]byte, 1014)
				n, _ := resp.Body.Read(bs)
				fmt.Println(string(bs[:n]))
				fmt.Println("Выполнил запрос: ", "http://", asReq.Host, ":", asReq.Port, asReq.Url)
				resp.Body.Close()

			}
			PullAR = PullAR[1:]
		}
	}

}

func main() {
	go TransleterAR()
	fmt.Println("горутина стартанула")
	router := mux.NewRouter()
	router.HandleFunc("/api/request/create", requestCreate)
	http.Handle("/", router)
	fmt.Println("Server is listening...")

	http.ListenAndServe(":8181", nil)

	// запустить горутину
	// которая будет запросы отправлять

}
