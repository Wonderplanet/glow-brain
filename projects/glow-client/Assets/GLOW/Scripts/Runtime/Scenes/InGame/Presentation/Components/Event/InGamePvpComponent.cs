using GLOW.Core.Presentation.Components;
using GLOW.Scenes.InGame.Domain.Models;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class InGamePvpComponent : UIObject, IInGameStageTimeDelegate
    {
        [SerializeField] InGameStageTimeComponent _time;

        public void Initialize(StageTimeModel model)
        {
            _time.Initialize(model.StageTimeLimit, model.RemainingTimeTextColor);
        }

        void IInGameStageTimeDelegate.UpdateTimeLimit(StageTimeModel model)
        {
            var countDownTime = model.StageTimeLimit - model.ElapsedTime;
            _time.UpdateTime(countDownTime, model.RemainingTimeTextColor);
        }
    }
}
