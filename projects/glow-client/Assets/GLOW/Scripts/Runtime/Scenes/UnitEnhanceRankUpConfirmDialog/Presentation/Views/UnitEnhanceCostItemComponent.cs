using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.UnitLevelUpDialogView.Presentation.Views;
using UnityEngine;
using UnityEngine.Serialization;
using UnityEngine.UI;

namespace GLOW.Scenes.UnitEnhanceRankUpConfirmDialog.Presentation.Views
{
    public class UnitEnhanceCostItemComponent : UIObject
    {
        [SerializeField] ItemIconComponent _itemIcon;
        [SerializeField] Button _itemIconButton;
        [SerializeField] UnitEnhanceItemPossessionComponent _itemPossession;

        public void Setup(ItemIconViewModel iconViewModel, ItemAmount amount)
        {
            _itemIcon.Setup(iconViewModel.ItemIconAssetPath, iconViewModel.Rarity, iconViewModel.Amount);
            _itemPossession.SetupItem(amount, iconViewModel.Amount);

            _itemIconButton.onClick.RemoveAllListeners();
            _itemIconButton.onClick.AddListener(() =>
            {
                ItemDetailUtil.Main.ShowItemDetailView(
                    ResourceType.Item,
                    iconViewModel.ItemId,
                    iconViewModel.Amount.ToPlayerResourceAmount());
            });
        }
    }
}
