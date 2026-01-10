using GLOW.Core.Presentation.Components;
using GLOW.Scenes.IdleIncentiveTop.Domain.ValueObjects;
using UnityEngine;

namespace GLOW.Scenes.IdleIncentiveTop.Presentation.Views
{
    public class IdleIncentiveTopButtonControl : UIObject
    {
        [Header("クイック探索")]
        [SerializeField] UITextButton _quickRewardButton;
        [Header("探索")]
        [SerializeField] UITextButton _rewardReceiveButton;
        [SerializeField] GameObject _receiveText;
        [Header("探索/受取不可")]
        [SerializeField] UIText _intervalTimeLabel;
        [SerializeField] GameObject _intervalTimeLabelObject;

        public void Setup(EnableQuickReceiveFlag enableQuickRewardReceive)
        {
            _quickRewardButton.interactable = enableQuickRewardReceive;
        }

        public void UpdateInterval(string intervalTime)
        {
            _receiveText.SetActive(string.IsNullOrEmpty(intervalTime));
            _rewardReceiveButton.interactable = string.IsNullOrEmpty(intervalTime);
            _intervalTimeLabelObject.SetActive(!string.IsNullOrEmpty(intervalTime));
            _intervalTimeLabel.SetText(intervalTime);
        }
    }
}
