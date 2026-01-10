using System.Collections.Generic;

namespace WPFramework.Modules.Benchmark
{
    public record ObservabilityKitRecordHttpTransactionData(
        string URLString,
        string HttpMethodString,
        double StartMilliSecs,
        double EndMilliSecs,
        IDictionary<string, string> HeaderDictionary,
        int StatusCode,
        int BytesSent,
        int BytesReceived,
        string ResponseData)
    {
        public string URLString { get; } = URLString;
        public string HttpMethodString { get; } = HttpMethodString;
        public double StartMilliSecs { get; } = StartMilliSecs;
        public double EndMilliSecs { get; } = EndMilliSecs;
        public IDictionary<string, string> HeaderDictionary { get; } = HeaderDictionary;
        public int StatusCode { get; } = StatusCode;
        public int BytesSent { get; } = BytesSent;
        public int BytesReceived { get; } = BytesReceived;
        public string ResponseData { get; } = ResponseData;
    }
}
