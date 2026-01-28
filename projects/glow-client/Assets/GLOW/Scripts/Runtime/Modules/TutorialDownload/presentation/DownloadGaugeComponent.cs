using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Modules.TutorialDownload.presentation
{
    public class DownloadGaugeComponent : UIObject
    {
        [SerializeField] UIText _progressText;
        [SerializeField] UIText _completedText;
        [SerializeField] UIText _percentText;
        [SerializeField] UIImage _gauge;
        
        public void SetGauge(DownloadProgress fillAmount)
        {
            _percentText.SetText(fillAmount.ToPercentageString());
            _gauge.Image.fillAmount = fillAmount.ToRate();
        }
        
        public void ShowCompletedDownloadText()
        {
            _progressText.Hidden = true;
            _completedText.Hidden = false;
        }
    }
}