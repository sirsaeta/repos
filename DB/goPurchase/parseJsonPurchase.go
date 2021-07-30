package main

import (
	"encoding/json"
	"fmt"
	"io/ioutil"
	"os"
	"strconv"
)

// Purchase a character from Day of Purchases
type Purchase struct {
	IdBase            interface{} `json:"_id"`
	PurchaseId        string      `json:"id"`
	LegacyId          string      `json:"legacyId"`
	Version           int         `json:"version"`
	Contact           interface{} `json:"contact"`
	CreationDate      interface{} `json:"creationDate"`
	Customer          interface{} `json:"customer"`
	Delivery          interface{} `json:"delivery"`
	DocumentNumber    string      `json:"documentNumber"`
	DocumentType      string      `json:"documentType"`
	Flow              string      `json:"flow"`
	GoogleAnalyticsId interface{} `json:"googleAnalyticsId"`
	Payment           interface{} `json:"payment"`
	Products          interface{} `json:"products"`
	Status            interface{} `json:"status"`
}

func (t Purchase) toString() string {
	bytes, err := json.Marshal(t)
	if err != nil {
		fmt.Println(err.Error())
		os.Exit(1)
	}
	return string(bytes)
}

func getPurchases() []Purchase {
	purchases := make([]Purchase, 3)
	raw, err := ioutil.ReadFile("../purchase.json")
	if err != nil {
		fmt.Println(err.Error())
		os.Exit(1)
	}
	json.Unmarshal(raw, &purchases)
	return purchases
}

func main() {
	purchases := getPurchases()
	//fmt.Println(productOrders)
	for _, pu := range purchases {
		if pu.Flow == "FULL" {
			fmt.Println(pu.toString())
			ioutil.WriteFile("../../ParsePurchase/"+pu.PurchaseId+"-v"+strconv.Itoa(pu.Version)+".json", []byte(pu.toString()), 0644)
		}
	}
}
