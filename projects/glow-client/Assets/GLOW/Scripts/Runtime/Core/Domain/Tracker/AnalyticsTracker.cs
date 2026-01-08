using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using WonderPlanet.AnalyticsBridge;
using WonderPlanet.AnalyticsBridge.Adjust;
using WonderPlanet.AnalyticsBridge.AnalyticsFirebase;
using Zenject;

namespace GLOW.Core.Domain.Tracker
{
    public interface IAnalyticsTracker
    {
        void TrackRevenueAdjustEvent(string eventName, double price, string currency);
        void TrackAdjustEvent(string eventName, Dictionary<string, object> param);
        void TrackFirebaseAnalyticsEvent(string eventName, Dictionary<string, object> param);
    }

    public class AnalyticsTracker : IAnalyticsTracker
    {
        [Inject] AnalyticsCenter AnalyticsCenter { get; }

        public void TrackRevenueAdjustEvent(string eventName, double price, string currency)
        {
            AnalyticsCenter.GetAgent<AdjustAgent>().Payment(eventName, price, currency);
        }

        public void TrackAdjustEvent(string eventName, Dictionary<string, object> param)
        {
            AnalyticsCenter.GetAgent<AdjustAgent>().Track(eventName, param);

            // TODO: 下記に置き換える
            // try
            // {
            //     AnalyticsCenter.GetAgent<AdjustAgent>().Track(eventName, param);
            // }
            // catch (Exception e)
            // {
            //     // TODO: エラー発生時の処理
            // }
        }

        public void TrackFirebaseAnalyticsEvent(string eventName, Dictionary<string, object> param)
        {
            AnalyticsCenter.GetAgent<FirebaseAgent>().Track(eventName, param);

            // TODO: 下記に置き換える
            // try
            // {
            //     AnalyticsCenter.GetAgent<FirebaseAgent>().Track(eventName, param);
            // }
            // catch (Exception e)
            // {
            //     // TODO: エラー発生時の処理
            // }
        }
    }
}
