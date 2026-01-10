using GLOW.Core.Domain.Modules.Time;
using GLOW.Core.Domain.ValueObjects;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Core.Presentation.Components
{
    public class EventCampaignBalloon : UIObject
    {
        [SerializeField] UIText _remainingTimeText;
        [SerializeField] UIText _discriptionText;

        public void SetRemainingTimeText(RemainingTimeSpan remainingTimeSpan)
        {
            var remainingTimeText = TimeSpanFormatter.FormatRemaining(remainingTimeSpan);
            _remainingTimeText.SetText(remainingTimeText);

            LayoutRebuilder.ForceRebuildLayoutImmediate(_discriptionText.RectTransform);
        }
    }
}
