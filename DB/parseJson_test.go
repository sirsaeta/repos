package main

import "testing"

// TestReadJSON testing read from JSON file
func TestReadJSON(t *testing.T) {
	productOrders := getProductOrders()
	productOrder := productOrders[0]
	expectedExternalId := "ECd-4b0z41Rsq6+ciw80zf9A-v2"
	// expectedSlug := "samsung-galaxy-a32-negro-128-gb"
	if expectedExternalId != productOrder.ExternalId {
		t.Errorf("productOrder.ExternalId == %q, want %q",
			productOrder.ExternalId, expectedExternalId)
	}
	// if expectedSlug != productOrder.Slug {
	// 	t.Errorf("productOrder.slug == %q, want %q",
	// 		productOrder.Slug, expectedSlug)
	// }
}
