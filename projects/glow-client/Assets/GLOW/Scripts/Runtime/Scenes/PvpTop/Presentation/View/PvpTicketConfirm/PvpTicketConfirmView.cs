using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Core.Presentation.Components;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.PvpTop.Presentation.View.PvpTicketConfirm
{
    public class PvpTicketConfirmView : UIView
    {
        [Header("タイトル")]
        [SerializeField] UIText _titleText;
        [Header("説明文")]
        [SerializeField] UIText _descriptionText;
        [Header("消費アイテム")]
        [SerializeField] UIText _beforeAmountText;
        [SerializeField] UIText _afterAmountText;
        [Header("チケット不足時の文言")]
        [SerializeField] UIObject _insufficientText;
        [Header("画面遷移")]
        [SerializeField] GameObject _transitArea;
        [Header("ボタン/ボタンテキスト")]
        [SerializeField] Button _applyButton;
        [SerializeField] UIText _cancelButtonText;

        public void SetUpTitleText(bool isSufficient)
        {
            var title = isSufficient ? "挑戦確認" : "ランクマッチチケット確認";
            _titleText.SetText(title);
        }

        public void SetUpDescriptionText(PvpItemChallengeCost pvpItemChallengeCost)
        {
            var format = "ランクマッチチケットを{0}枚使用して\nランクマッチに1回挑戦しますか？";
            _descriptionText.SetText(format, pvpItemChallengeCost.Value);
        }

        public void SetUpAmountTexts(
            ItemAmount itemAmount,
            PvpItemChallengeCost pvpItemChallengeCost)
        {
            _beforeAmountText.SetText(itemAmount.Value.ToString());
            var afterAmount = itemAmount.Value - pvpItemChallengeCost.Value;
            afterAmount = Mathf.Max(0, afterAmount);
            _afterAmountText.SetText(afterAmount.ToString());
        }

        public void SetUpTransitAreaActive(bool isActive)
        {
            _transitArea.SetActive(isActive);
        }
        public void SetUpApplyButtonActive(bool isActive)
        {
            _applyButton.gameObject.SetActive(isActive);
            var cancelButtonText = isActive ? "キャンセル" : "閉じる";
            _cancelButtonText.SetText(cancelButtonText);
        }

        public void SetInsufficientTextActive(bool isActive)
        {
            _insufficientText.gameObject.SetActive(isActive);
        }
    }
}
