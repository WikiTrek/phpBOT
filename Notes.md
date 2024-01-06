# Miscellanea

## TODO
* [] Use query https://w.wiki/8hdL to generate Wikidata links and add it to DataTrek

## Programming
Creates a new claim on the item for the property P56 and a value of "ExampleString"
    api.php?action=wbeditentity&id=Q4115189&data={"claims":
    [{
        "mainsnak":
            {"snaktype":"value","property":"P56","datavalue":
                {"value":"ExampleString","type":"string"}
            },
            "type":"statement","rank":"normal"
    }]
    }
Sets the claim with the GUID to the value of the claim
    api.php?action=wbeditentity&id=Q4115189&data={"claims":
    [{
        "id":
            "Q4115189$GH678DSA-01PQ-28XC-HJ90-DDFD9990126X",
        "mainsnak":
            {"snaktype":"value","property":"P56","datavalue":
                {"value":"ChangedString","type":"string"}
            },
            "type":"statement","rank":"normal"
    }]
    }