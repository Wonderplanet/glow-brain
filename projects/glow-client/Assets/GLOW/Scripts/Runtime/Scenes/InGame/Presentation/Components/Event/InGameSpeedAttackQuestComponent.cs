using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.InGame.Domain.Models;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class InGameSpeedAttackQuestComponent : UIObject,
        IInGameStageTimeDelegate
    {
        [SerializeField] InGameStageTimeComponent _time;

        public void Initialize(StageTimeModel model)
        {
            _time.Initialize(model.ElapsedTime, model.RemainingTimeTextColor);
        }

        void IInGameStageTimeDelegate.UpdateTimeLimit(StageTimeModel model)
        {
            if (model.RemainingTime <= InGameTimeLimit.Zero)
            {
                _time.UpdateTime(model.StageTimeLimit.ToLimitTimeSeconds(), model.RemainingTimeTextColor);
                return;
            }

            _time.UpdateTime(model.ElapsedTime, model.RemainingTimeTextColor);
        }
    }
}
