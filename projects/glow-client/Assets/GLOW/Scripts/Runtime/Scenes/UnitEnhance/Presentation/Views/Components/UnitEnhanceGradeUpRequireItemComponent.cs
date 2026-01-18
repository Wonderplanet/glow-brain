using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules;
using GLOW.Core.Presentation.ViewModels;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.UnitEnhance.Presentation.Views.Components
{
    public class UnitEnhanceGradeUpRequireItemComponent : MonoBehaviour
    {
        [SerializeField] UIText _requireItemNum;
        [SerializeField] UIText _requireItemName;
        [SerializeField] Slider _requireItemSlider;
        [SerializeField] SimpleItemIconComponent _itemIcon;
        [SerializeField] Button _itemIconButton;

        public void Setup(
            ItemIconViewModel itemIconViewModel,
            ItemAmount requireItemAmount,
            ItemAmount possessionItemAmount,
            ItemName itemName)
        {
            _itemIcon.Setup(itemIconViewModel.ItemIconAssetPath, itemIconViewModel.Rarity);
            _requireItemNum.SetText($"{possessionItemAmount.Value}/{requireItemAmount.Value}");
            _requireItemName.SetText(itemName.Value);
            _requireItemSlider.value = (float)possessionItemAmount.Value / requireItemAmount.Value;

            _itemIconButton.onClick.RemoveAllListeners();
            _itemIconButton.onClick.AddListener(() =>
            {
                ItemDetailUtil.Main.ShowItemDetailView(
                    ResourceType.Item,
                    itemIconViewModel.ItemId,
                    itemIconViewModel.Amount.ToPlayerResourceAmount());
            });
        }
    }
}
