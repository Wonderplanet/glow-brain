using System;
using UnityEngine;
using UnityEngine.UI;
using WonderPlanet.PerformanceProfiler.Metric;

namespace WPFramework.Debugs.Profiler
{
    public sealed class DebugSystemUsageComponent : MonoBehaviour
    {
        [SerializeField] Text _text;
        [SerializeField] Image _background;

        Color _defaultColor;
        MetricStatus _currentStatus;

        public void Awake()
        {
            _defaultColor = _background.color;
            SetMetricStatus(MetricStatus.Emergency);
        }

        public void SetText(string text)
        {
            _text.text = text;
        }

        public void SetMetricStatus(MetricStatus status)
        {
            if (_currentStatus == status)
            {
                return;
            }

            switch (status)
            {
                case MetricStatus.Normal:
                    Normal();
                    break;
                case MetricStatus.Warning:
                    Warning();
                    break;
                case MetricStatus.Emergency:
                    Emergency();
                    break;
                default:
                    Normal();
                    break;
            }

            _currentStatus = status;
        }

        void Emergency()
        {
            _background.color = Color.red;
        }

        void Warning()
        {
            _background.color = Color.yellow;
        }

        void Normal()
        {
            _background.color = _defaultColor;
        }
    }
}
