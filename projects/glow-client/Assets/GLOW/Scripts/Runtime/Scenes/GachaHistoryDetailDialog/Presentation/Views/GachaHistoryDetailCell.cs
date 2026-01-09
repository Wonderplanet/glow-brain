using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.GachaHistoryDetailDialog.Presentation.ViewModels;
using UnityEngine;
using WPFramework.Presentation.Modules;
using AmountFormatter = GLOW.Core.Presentation.Modules.AmountFormatter;

namespace GLOW.Scenes.GachaHistoryDetailDialog.Presentation.Views
{
    public class GachaHistoryDetailCell : UIObject
    {
        [SerializeField] PlayerResourceIconComponent _playerResourceIconComponent;
        [SerializeField] UIText _nameText;
        [SerializeField] UIText _coinNameText;
        [SerializeField] UIImage _iconImage;
        [SerializeField] UIText _amountText;
        [SerializeField] GameObject _acquiredTextObject;
        [SerializeField] GameObject _amountObject;
        [SerializeField] RarityIconLeftAlignComponent _rarityIconLeftAlignComponent;
        
        public void Setup(GachaHistoryDetailCellViewModel detailCellViewModel)
        {
            _playerResourceIconComponent.Setup(detailCellViewModel.PlayerResourceIconViewModel);
            
            // アイコン上に個数表示させないため、Emptyをセットする
            _playerResourceIconComponent.SetAmount(PlayerResourceAmount.Empty);
            
            // コインの場合はレアリティ表示しない
            var isCoin = detailCellViewModel.PlayerResourceIconViewModel.ResourceType == ResourceType.Coin;
            SetupNameText(detailCellViewModel.GetDisplayName(), isCoin);
            SetupRarityIcon(detailCellViewModel.PlayerResourceIconViewModel.Rarity, isCoin);
            
            // 獲得個数側のアイコン設定
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(
                _iconImage.Image,
                detailCellViewModel.AcquiredPlayerResourceIconAssetPath.Value);
            
            _amountText.SetText(
                "×{0}",
                AmountFormatter.FormatAmount(detailCellViewModel.AcquiredPlayerResourceAmount.Value));
            
            // 未獲得ユニットでアセットパスが空の場合は、獲得数を非表示にする
            _amountObject.SetActive(detailCellViewModel.ShouldShowAmountObject());
            
            _acquiredTextObject.SetActive(detailCellViewModel.IsAcquiredUnit());
        }
        
        void SetupNameText(string name, bool isCoin)
        {
            _nameText.Hidden = isCoin;
            _coinNameText.Hidden = !isCoin;
            
            if (isCoin)
            {
                _coinNameText.SetText(name);
            }
            else
            {
                _nameText.SetText(name);
            }
        }
        
        void SetupRarityIcon(Rarity rarity, bool isCoin)
        {
            _rarityIconLeftAlignComponent.Hidden = isCoin;
            if (!isCoin)
            {
                _rarityIconLeftAlignComponent.Setup(rarity);
            }
        }
    }
}