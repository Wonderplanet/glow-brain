using Cysharp.Text;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.BoxGacha;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.BoxGacha.Domain.ValueObject;
using UIKit;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.BoxGachaConfirm.Presentation.View
{
    public class BoxGachaConfirmDialogView : UIView
    {
        [SerializeField] UIText _titleText;
        [SerializeField] UIText _confirmText;
        [SerializeField] UIText _shortageAttentionText;
        [SerializeField] AmountSelectionComponent _amountSelectionComponent;
        [SerializeField] Button _drawButton;
        
        [Header("使用アイテムの交換前後表示")]
        [SerializeField] UIImage _itemIconImage;
        [SerializeField] UIText _beforeOfferCostAmountText;
        [SerializeField] UIText _afterOfferCostAmountText;
        
        public GachaDrawCount GachaDrawCount => new (_amountSelectionComponent.Amount.Value);

        ItemName _costItemName;
        ItemAmount _offerCostItemAmount;
        BoxGachaName _boxGachaName;
        CostAmount _costItemAmount;

        public void Initialize(
            ItemName costItemName,
            ItemAmount offerCostItemAmount,
            CostAmount costItemAmount,
            BoxGachaName boxGachaName)
        {
            _costItemName = costItemName;
            _offerCostItemAmount = offerCostItemAmount;
            _boxGachaName = boxGachaName;
            _costItemAmount = costItemAmount;
        }

        public void SetUpTitle(BoxGachaName boxGachaName)
        {
            _titleText.SetText(ZString.Format("{0}確認", boxGachaName.ToString()));
        }

        public void SetUpConfirmText(
            ItemName costItemName,
            CostAmount costItemAmount,
            BoxGachaName boxGachaName,
            GachaDrawCount drawCount)
        {
            var totalCostItemAmount = costItemAmount * drawCount;
            _confirmText.SetText(ZString.Format(
                "{0}を{1}個使用して\n{2}を{3}回引きますか？",
                costItemName.ToString(),
                totalCostItemAmount,
                boxGachaName.ToString(),
                drawCount.Value));
        }
        
        public void SetUpShortageAttentionTextAndButton(
            ItemName costItemName,
            BoxGachaDrawableFlag isDrawable)
        {
            _shortageAttentionText.IsVisible = !isDrawable;
            _shortageAttentionText.SetText(ZString.Format(
                "{0}が不足しています。",
                costItemName.ToString()));
            _drawButton.interactable = isDrawable;
        }
        
        public void SetUpCostItemIcon(ItemIconAssetPath costItemIconAssetPath)
        {
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(
                _itemIconImage.Image,
                costItemIconAssetPath.Value);
        }
        
        public void SetUpOfferCostItemAmountDisplay(
            ItemAmount offerCostItemAmount,
            CostAmount costItemAmount,
            GachaDrawCount drawCount)
        {
            _beforeOfferCostAmountText.SetText(offerCostItemAmount.ToString());
            var totalCostItemAmount = costItemAmount * drawCount;
            var afterAmount = new ItemAmount(offerCostItemAmount.Value - (int)totalCostItemAmount.Value);
            _afterOfferCostAmountText.SetText(afterAmount.ToString());
        }
        
        public void SetUpAmountSelection(
            GachaDrawCount maxDrawCount)
        {
            _amountSelectionComponent.Setup(
                ItemAmount.One,
                new ItemAmount(maxDrawCount.ClampToMax()),
                () =>
                {
                    var drawCount = new GachaDrawCount(_amountSelectionComponent.Amount.Value);
                    SetUpConfirmText(
                        _costItemName,
                        _costItemAmount,
                        _boxGachaName,
                        drawCount);
                    SetUpOfferCostItemAmountDisplay(
                        _offerCostItemAmount,
                        _costItemAmount,
                        drawCount);
                });
        }
        
    }
}