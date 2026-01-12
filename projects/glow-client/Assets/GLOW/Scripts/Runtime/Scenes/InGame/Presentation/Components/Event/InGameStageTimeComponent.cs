using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.InGame.Domain.Models;
using UnityEngine;
using UnityEngine.Serialization;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class InGameStageTimeComponent : UIObject
    {
        [SerializeField] UIText _timeText;

        public void Initialize(InGameTimeLimit timeLimit, RemainingTimeTextColor textColor)
        {
            SetTime(timeLimit);
            SetTextColor(textColor);
        }

        public void UpdateTime(InGameTimeLimit timeLimit, RemainingTimeTextColor textColor)
        {
            SetTime(timeLimit);
            SetTextColor(textColor);
        }

        public void UpdateTime(float time, RemainingTimeTextColor textColor)
        {
            SetText(time.ToString("F2"));
            SetTextColor(textColor);
        }

        void SetTime(InGameTimeLimit timeLimit)
        {
            SetText(timeLimit.ToRemainingTimeText());
        }

        void SetText(string timeLimit)
        {
            _timeText.SetText("{0}秒", timeLimit);
        }

        void SetTextColor(RemainingTimeTextColor textColor)
        {
            _timeText.CrossFadeColor(textColor.Color, 0.1f, false, true);
        }
    }
}
