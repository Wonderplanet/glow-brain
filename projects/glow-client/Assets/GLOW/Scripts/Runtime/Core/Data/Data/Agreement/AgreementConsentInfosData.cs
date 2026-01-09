using System;
using System.Runtime.Serialization;
using Newtonsoft.Json;

namespace GLOW.Core.Data.Data.Agreement
{
    [Serializable]
    public class AgreementConsentInfosData
    {
        [DataMember(Name = "ok")][JsonProperty("ok")]
        public bool Ok { get; set; }
        [DataMember(Name = "results")][JsonProperty("results")]
        public AgreementConsentInfoResultsData Results { get; set; }
    }
}
