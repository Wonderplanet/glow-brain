using Cysharp.Text;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.StaminaRecover.Presentation.StaminaRecoverySelect
{
    public class UseButtonStateComponent : UIObject
    {
        [SerializeField] Button _useButton;
        [SerializeField] GameObject _remainingTimeTextObject;
        [SerializeField] UIText _remainingTimeText;

        public Button MainButton => _useButton;

        public void SetUpButton(bool isAvailable, RemainingTimeSpan remainingTime)
        {
            _useButton.interactable = isAvailable;
            _remainingTimeTextObject.SetActive(false);

            if (remainingTime.IsEmpty()) return;

            _remainingTimeTextObject.SetActive(true);
            _remainingTimeText.SetText(GetTimeFormat(remainingTime));
        }

        string GetTimeFormat(RemainingTimeSpan intervalMinute)
        {
            return ZString.Format("{0:mm\\:ss}", intervalMinute.Value);
        }
    }
}
