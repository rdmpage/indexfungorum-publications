# indexfungorum-publications
Linking Index Fungorum names to the corresponding publications.

## Notes

### mEDRA DOIs

Some articles have mEDRA DOIs, such as _Sydowia_, e.g. http://dx.doi.org/10.12905/0380.sydowia66(2)2014-0241  These DOIs lack a discovery service, but do support content negotiation to retrieve metadata. For example setting accept to “application/vnd.citationstyles.csl+json” gives:

```javascript
{
    "type": "article-journal",
    "author": [
        {
            "family": "Xia",
            "given": "Ji-Wen",
            "literal": "Ji-Wen Xia"
        },
        {
            "family": "Ma",
            "given": "Ying-Rui",
            "literal": "Ying-Rui Ma"
        },
        {
            "family": "Zhang",
            "given": "Xiu-Guo",
            "literal": "Xiu-Guo Zhang"
        }
    ],
    "issued": {
        "date-parts": [
            [
                2014,
                12
            ]
        ]
    },
    "container-title": "Sydowia. An international Journal of Mycology",
    "DOI": "10.12905/0380.sydowia66(2)2014-0241",
    "ISSN": "0082-0598",
    "issue": "66",
    "page": "241-248",
    "page-first": "241",
    "publisher": "Verlag Ferdinand Berger & Söhne GmbH",
    "title": "New species of Corynesporopsis and Lylea from China",
    "URL": "http://doi.org/10.12905/0380.sydowia66(2)2014-0241"
}
```

## Plans

### Linking names to taxon concepts

For example, linking names to UNITE taxa, e.g. Jaminaea angkorensis Sipiczki & Kajdacsi | SH207864.07FU | [DOI: 10.15156/BIO/SH207864.07FU](http://dx.doi.org/10.15156/BIO/SH207864.07FU) which is linked to urn:lsid:indexfungorum.org:names:540618 (see https://unite.ut.ee/bl_forw_sh.php?sh_name=SH207864.07FU#fndtn-panel1). 

### UNITE fungal taxa

UNITE has an API where you can go from code (eventually) to data, including Index Fungorum LSIDs. See https://unite.ut.ee/repository.php for details.

### Linking to types in GBIF

E.g., http://www.gbif.org/occurrence/1212916944 which is CBS 10918 (also called C5b, see http://dx.doi.org/10.1099/ijs.0.003939-0 and https://www.ncbi.nlm.nih.gov/nuccore/EU587489
