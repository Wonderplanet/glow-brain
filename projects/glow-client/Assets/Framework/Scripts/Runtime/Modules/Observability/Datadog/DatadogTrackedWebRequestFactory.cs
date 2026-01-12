#if OBSERVABILITY_DATADOG_ENABLED
using System;
using UnityHTTPLibrary;

namespace WPFramework.Modules.Observability
{
    public sealed class DatadogTrackedWebRequestFactory : IHTTPRequestFactory
    {
        readonly ITLSCertificateHandler _certificateHandler;

        public DatadogTrackedWebRequestFactory() : this(null)
        {
        }

        public DatadogTrackedWebRequestFactory(ITLSCertificateHandler certificateHandler)
        {
            _certificateHandler = certificateHandler;
        }

        public IHTTPRequest CreateRequest(Uri uri)
        {
            return new DatadogTrackedWebRequest(uri, _certificateHandler);
        }
    }
}
#endif  // OBSERVABILITY_DATADOG_ENABLED
