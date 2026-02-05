using System;

namespace GLOW.Core.Modules.TimeScaleController
{
    public class TimeScaleControlHandler : ITimeScaleControlHandler
    {
        public float TimeScale { get; }
        public TimeScaleType Type { get; }
        public TimeScalePriority Priority { get; }
        public Action<TimeScaleControlHandler> DisposedCallback { get; set; }

        /// <summary>
        /// タイプと優先度を指定するコンストラクタ
        /// </summary>
        public TimeScaleControlHandler(float timeScale, TimeScaleType type, TimeScalePriority priority)
        {
            TimeScale = timeScale;
            Type = type;
            Priority = priority;
        }

        public void Dispose()
        {
            DisposedCallback?.Invoke(this);
        }
    }
}