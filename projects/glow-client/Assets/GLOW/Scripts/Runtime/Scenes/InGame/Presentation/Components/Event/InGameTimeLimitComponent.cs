using GLOW.Core.Presentation.Components;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class InGameTimeLimitComponent : UIObject, IInGameStageTimeDelegate
    {
        [SerializeField] InGameStageTimeComponent _time;
        [SerializeField] UIText _endConditionText;

        public void Initialize(StageTimeModel model)
        {
            _time.Initialize(model.StageTimeLimit, model.RemainingTimeTextColor);
            _endConditionText.SetText(model.Rule == InGameTimeRule.TimeLimitDefeat
            ? "制限時間以内にクリアしよう！！"
            : "制限時間まで生き残れ！！");
        }

        void IInGameStageTimeDelegate.UpdateTimeLimit(StageTimeModel model)
        {
            var countDownTime = model.StageTimeLimit - model.ElapsedTime;
            _time.UpdateTime(countDownTime, model.RemainingTimeTextColor);
        }
    }
}
