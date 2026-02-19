using System.Collections.Generic;
using UnityEngine;
using WonderPlanet.ObservabilityKit;

namespace WPFramework.Modules.Benchmark
{
    internal sealed class ObservabilityKitRecordHttpTransactionDispatcher : MonoBehaviour
    {
        readonly Queue<ObservabilityKitRecordHttpTransactionData> _recordHttpTransactionQueue =
            new Queue<ObservabilityKitRecordHttpTransactionData>();

        public void AddRecordHttpTransaction(ObservabilityKitRecordHttpTransactionData recordHttpTransaction)
        {
            _recordHttpTransactionQueue.Enqueue(recordHttpTransaction);
        }

        void Update()
        {
            // NOTE: キューイングされている通信時の情報を送信する
            while (_recordHttpTransactionQueue.Count > 0)
            {
                var recordHttpTransaction = _recordHttpTransactionQueue.Dequeue();
                ObservabilityKit.RecordHttpTransaction(
                    recordHttpTransaction.URLString,
                    recordHttpTransaction.HttpMethodString,
                    recordHttpTransaction.StartMilliSecs,
                    recordHttpTransaction.EndMilliSecs,
                    recordHttpTransaction.HeaderDictionary,
                    recordHttpTransaction.StatusCode,
                    recordHttpTransaction.BytesSent,
                    recordHttpTransaction.BytesReceived,
                    recordHttpTransaction.ResponseData);
            }
        }
    }
}
