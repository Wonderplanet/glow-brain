using System;
using System.Collections.Generic;
using Datadog.Unity;
using Datadog.Unity.Rum;
using UnityEngine.Networking;
using WPFramework.Modules.Log;

namespace WPFramework.Modules.Observability
{
    internal sealed class DatadogWebRequestTracker
    {
        public string RumKey { get; private set; } = string.Empty;

        public void StartResource(UnityWebRequest webRequest)
        {
            var attributes = new Dictionary<string, object>();

            try
            {
                // Datadogのトレースを開始
                RumKey = Guid.NewGuid().ToString();

                DatadogSdk.Instance.Rum.StartResource(
                    RumKey,
                    EnumHelpers.HttpMethodFromString(webRequest.method),
                    webRequest.url,
                    attributes);
            }
            catch (Exception e)
            {
                ApplicationLog.LogError(nameof(DatadogTrackedWebRequest), $"Failed to start resource: {e}");
            }
        }

        public void StopResource(UnityWebRequest webRequest)
        {
            if (string.IsNullOrEmpty(RumKey))
            {
                ApplicationLog.LogWarning(nameof(DatadogTrackedWebRequest), "RumKey is empty");
                return;
            }

            try
            {
                switch (webRequest.result)
                {
                    case UnityWebRequest.Result.Success:
                    case UnityWebRequest.Result.ProtocolError:
                        var contentType = webRequest.GetResponseHeader("content-type");
                        DatadogSdk.Instance.Rum.StopResource(
                            RumKey,
                            EnumHelpers.ResourceTypeFromContentType(contentType),
                            (int)webRequest.responseCode,
                            (long)webRequest.downloadedBytes);
                        break;
                    case UnityWebRequest.Result.InProgress:
                        break;
                    case UnityWebRequest.Result.ConnectionError:
                    case UnityWebRequest.Result.DataProcessingError:
                    default:
                        DatadogSdk.Instance.Rum.StopResourceWithError(
                            RumKey,
                            webRequest.result.ToString(),
                            webRequest.error);
                        break;
                }
            }
            catch (Exception e)
            {
                ApplicationLog.LogError(nameof(DatadogTrackedWebRequest), $"Failed to stop resource: {e}");
            }
            finally
            {
                RumKey = string.Empty;
            }
        }
    }
}
