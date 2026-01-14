using GLOW.Scenes.PvpTop.Domain.ValueObject;
using UnityEngine;

namespace GLOW.Scenes.PvpTop.Presentation.Calculator
{
    public class PvpOpponentRefreshCoolTimeCalculator
    {
        float _availableTime;

        public void StartCalculate(PvpOpponentRefreshCoolTime coolTime)
        {
            var currentTime = Time.time;
            _availableTime = currentTime + coolTime.Value;
        }

        public PvpOpponentRefreshCoolTime Calculate()
        {
            var currentTime = Time.time;

            if (currentTime < _availableTime)
            {
                var remain = _availableTime - currentTime;
                var result = Mathf.Ceil(remain);
                return new PvpOpponentRefreshCoolTime(result);
            }
            else
            {
                //Emptyにする
                return PvpOpponentRefreshCoolTime.Empty;
            }
        }
    }
}
