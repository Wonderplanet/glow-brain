using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class InGameEnhanceQuestComponent : UIObject,
        IInGameStageTimeDelegate,
        IInGameStageScoreDelegate
    {
        [SerializeField] InGameScoreComponent _score;
        [SerializeField] InGameStageTimeComponent _time;

        InGameScore _currentScore = InGameScore.Zero;

        public void Initialize(StageTimeModel model)
        {
            _score.Initialize();
            _time.Initialize(model.StageTimeLimit, model.RemainingTimeTextColor);
        }

        void IInGameStageTimeDelegate.UpdateTimeLimit(StageTimeModel model)
        {
            var countDownTime = model.StageTimeLimit - model.ElapsedTime;
            _time.UpdateTime(countDownTime, model.RemainingTimeTextColor);
        }

        void IInGameStageScoreDelegate.UpdateScore(InGameScore score)
        {
            // 表示反映のタイミングで現在の表示スコアより小さいスコアが送られる場合は反映中止
            if (_currentScore >= score)
            {
                return;
            }
            
            _currentScore = score;
            _score.SetScore(score);
        }
    }
}
