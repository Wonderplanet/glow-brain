using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Extensions;

namespace GLOW.Core.Modules.TimeScaleController
{
    public class TimeScaleController : ITimeScaleController, IDisposable
    {
        readonly List<TimeScaleControlHandler> _handlers = new ();
        readonly ITimeScaleApplier _timeScaleApplier;
        
        public TimeScaleController(ITimeScaleApplier timeScaleApplier)
        {
            _timeScaleApplier = timeScaleApplier;
        }


        /// <summary>
        /// TimeScaleを変更する（タイプと優先度を指定）
        /// </summary>
        public ITimeScaleControlHandler ChangeTimeScale(float timeScale, TimeScaleType type, TimeScalePriority priority)
        {
            var handler = new TimeScaleControlHandler(timeScale, type, priority);
            handler.DisposedCallback = OnHandlerDisposed;
            
            _handlers.Insert(0, handler);
            ApplyTimeScale();

            return handler;
        }

        public void Dispose()
        {
            _handlers.ForEach(handler => handler.DisposedCallback = null);
            _handlers.Clear();
            
            _timeScaleApplier?.SetTimeScale(1f);
        }

        void OnHandlerDisposed(TimeScaleControlHandler handler)
        {
            handler.DisposedCallback = null;
            
            _handlers.Remove(handler);
            ApplyTimeScale();
        }

        void ApplyTimeScale()
        {
            if (_handlers.Count == 0)
            {
                _timeScaleApplier.SetTimeScale(1f);
                return;
            }

            // 基準となるTimeScaleを決定
            float baseTimeScale;
            TimeScalePriority basePriority;
            
            // Fixedタイプのハンドラーから最も優先度の高いものを取得
            var highestPriorityFixed = _handlers
                .Where(h => h.Type == TimeScaleType.Fixed)
                .MaxBy(h => h.Priority.Value);
            
            if (highestPriorityFixed != null)
            {
                // 最も優先度の高いFixedハンドラーの値を基準とする
                baseTimeScale = highestPriorityFixed.TimeScale;
                basePriority = highestPriorityFixed.Priority;
            }
            else
            {
                // Fixedハンドラーがない場合は1.0を基準とする
                baseTimeScale = 1f;
                basePriority = TimeScalePriority.Min;
            }

            // 基準より優先度の高いMultiplyハンドラーを適用
            var multiplyHandlers = _handlers
                .Where(h => h.Type == TimeScaleType.Multiply && h.Priority > basePriority)
                .ToList();

            var finalTimeScale = multiplyHandlers.Aggregate(baseTimeScale, 
                (current, handler) => current * handler.TimeScale);

            _timeScaleApplier.SetTimeScale(finalTimeScale);
        }
    }
}