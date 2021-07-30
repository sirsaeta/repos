package main

import (
	"encoding/json"
	"fmt"
	"io/ioutil"
	"os"
)

// ProductOrder a character from Day of ProductOrders
type ProductOrder struct {
	ExternalId                 string      `json:"externalId"`
	Id                         interface{} `json:"_id"`
	OriginalSubmitOrderRequest interface{} `json:"originalSubmitOrderRequest"`
	StatusHistory              interface{} `json:"statusHistory"`
}

func (t ProductOrder) toString() string {
	bytes, err := json.Marshal(t)
	if err != nil {
		fmt.Println(err.Error())
		os.Exit(1)
	}
	return string(bytes)
}

func getProductOrders() []ProductOrder {
	productOrders := make([]ProductOrder, 3)
	raw, err := ioutil.ReadFile("./productOrders.json")
	if err != nil {
		fmt.Println(err.Error())
		os.Exit(1)
	}
	json.Unmarshal(raw, &productOrders)
	return productOrders
}

func main() {
	productOrders := getProductOrders()
	//fmt.Println(productOrders)
	for _, po := range productOrders {
		fmt.Println(po.toString())
		ioutil.WriteFile("../OrderParse/"+po.ExternalId+".json", []byte(po.toString()), 0644)
	}
}
