using System;
using System.Collections.Generic;
using UnityEngine;
using UnityHTTPLibrary;
using WPFramework.Modules.Log;
using Object = UnityEngine.Object;

namespace WPFramework.Modules.Benchmark
{
    public sealed class ObservabilityKitHttpTaskEventDelegate : IRequestTaskEventDelegate
    {
        static ObservabilityKitRecordHttpTransactionDispatcher _dispatcher;

        readonly Dictionary<object, double> _taskStartTime = new Dictionary<object, double>();

        static readonly DateTime UtcReferenceTime = new DateTime(1970, 1, 1, 0, 0, 0, DateTimeKind.Utc);

        void IRequestTaskEventDelegate.OnRequestTaskStart(IRequestTaskHandler handler)
        {
            // NOTE: キューイングして送信するためのDispatcherを生成する
            if (!_dispatcher)
            {
                const string name = "[ObservabilityKit] RecordHttpTransactionDispatcher";
                _dispatcher = new GameObject(name).AddComponent<ObservabilityKitRecordHttpTransactionDispatcher>();
                Object.DontDestroyOnLoad(_dispatcher);
            }

            // NOTE: 通信開始時の時間を保持する
            var currentTime = (DateTime.UtcNow - UtcReferenceTime).TotalMilliseconds;
            if (handler.Request == null)
            {
                return;
            }

            object key = handler.Request.Uri;
            _taskStartTime[key] = currentTime;
        }

        void IRequestTaskEventDelegate.OnRequestTaskEnd(IRequestTaskHandler handler)
        {
            // NOTE: URLをキーとして通信開始時の時間を取得する
            var request = handler.Request;
            object key = request.Uri;

            var endTime = (DateTime.UtcNow - UtcReferenceTime).TotalMilliseconds;
            var response = request.Response;
            var reqData = request.Data;
            var bytesSent = 0;
            if (reqData != null)
            {
                bytesSent = reqData.Length;
            }

            var responseData = response.DataAsText;
            var bytesReceived = responseData.Length;

            var totalMillSecond = endTime - _taskStartTime[key];
            ApplicationLog.Log(nameof(ObservabilityKitHttpTaskEventDelegate), $"Request: {request.FullUrl()} {request.Method} {bytesSent} bytes, Response: {response.StatusCode} {bytesReceived} bytes, endTime: {totalMillSecond} ms");

            // NOTE: 利用許諾される前に通信した情報も送りはしないがキューイングして保持しておく
            _dispatcher.AddRecordHttpTransaction(new ObservabilityKitRecordHttpTransactionData(
                request.FullUrl(),
                request.Method.ToString().ToUpper(),
                _taskStartTime[key],
                endTime,
                response.Headers,
                (int)response.StatusCode,
                bytesSent,
                bytesReceived,
                responseData));
        }

        void IRequestTaskEventDelegate.OnRequestTaskComplete(IRequestTaskHandler handler)
        {
        }
    }
}
