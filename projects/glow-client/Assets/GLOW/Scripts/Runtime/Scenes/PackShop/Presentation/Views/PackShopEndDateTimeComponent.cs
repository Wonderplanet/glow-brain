using System;
using GLOW.Core.Domain.Modules.Time;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.PackShop.Presentation.Views
{
    public class PackShopEndDateTimeComponent : MonoBehaviour
    {
        [SerializeField] UIText _text;
        [SerializeField] UIText _under24HoursText;

        public void UpdateEndTime(TimeSpan endDateTime)
        {
            var text = TimeSpanFormatter.FormatRemaining(endDateTime);
            var isUnder24Hours = endDateTime.TotalDays < 1;
            _text.SetText(text);
            _under24HoursText.SetText(text);

            _text.IsVisible = !isUnder24Hours;
            _under24HoursText.IsVisible = isUnder24Hours;
        }

        public void SetEndTimeInfinity()
        {
            _text.SetText("期限なし");
        }
    }
}
