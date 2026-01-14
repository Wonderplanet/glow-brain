using UnityEngine;

namespace GLOW.Core.Modules.TimeScaleController
{
    public class TimeScaleApplier : ITimeScaleApplier
    {
        public void SetTimeScale(float timeScale)
        {
            Time.timeScale = timeScale;
        }
    }
}