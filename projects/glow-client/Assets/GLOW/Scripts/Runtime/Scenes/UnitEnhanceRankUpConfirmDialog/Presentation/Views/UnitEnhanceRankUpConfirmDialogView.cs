using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.UnitEnhance.Presentation.Views.Components;
using GLOW.Scenes.UnitEnhanceRankUpConfirmDialog.Presentation.ViewModels;
using GLOW.Scenes.UnitLevelUpDialogView.Presentation.Views;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.UnitEnhanceRankUpConfirmDialog.Presentation.Views
{
    public class UnitEnhanceRankUpConfirmDialogView : UIView
    {
        [Header("レベル")]
        [SerializeField] UIText _beforeLimitLevel;
        [SerializeField] UIText _afterLimitLevel;

        [Header("要求アイテム")]
        [SerializeField] List<UnitEnhanceCostItemComponent> _costItem;
        [SerializeField] ChildScaler _costItemChildScaler;

        [Header("ステータス")]
        [SerializeField] UnitEnhanceBaseStatusPreviewComponent _baseStatusPreview;
        [SerializeField] UnitEnhanceSpecialStatusPreviewComponent _specialStatusPreview;

        [Header("特性")]
        [SerializeField] UnitEnhanceAbilityComponent _abilityComponent;

        [Header("上限開放ボタン")]
        [SerializeField] UITextButton _confirmButton;

        public void Setup(UnitEnhanceRankUpConfirmViewModel viewModel)
        {
            _beforeLimitLevel.SetText(viewModel.BeforeLimitLevel.ToStringWithPrefixLv());
            _afterLimitLevel.SetText(viewModel.AfterLimitLevel.ToStringWithPrefixLv());

            var isSpecial = viewModel.RoleType == CharacterUnitRoleType.Special;
            _baseStatusPreview.Hidden = isSpecial;
            _specialStatusPreview.Hidden = !isSpecial;
            _baseStatusPreview.SetupHP(viewModel.BeforeHp, viewModel.AfterHp);
            _baseStatusPreview.SetupAttackPower(viewModel.BeforeAttackPower, viewModel.AfterAttackPower);
            _specialStatusPreview.Setup(viewModel.BeforeAttackPower, viewModel.AfterAttackPower);
            _abilityComponent.Setup(viewModel.UnitAbilities);

            _confirmButton.interactable = viewModel.EnableConfirm.IsEnable();

            SetRequireItem(viewModel.CostItems);
        }
        
        public void PlayCostItemAppearanceAnimation()
        {
            _costItemChildScaler.Play();
        }

        void SetRequireItem(IReadOnlyList<UnitEnhanceCostItemViewModel> costItemViewModels)
        {
            for (int i = 0; i < _costItem.Count && i < costItemViewModels.Count; ++i)
            {
                var viewModel = costItemViewModels[i];
                _costItem[i].Setup(viewModel.ItemIcon, viewModel.PossessionAmount);
            }

            for (int i = costItemViewModels.Count ; i < _costItem.Count; ++i)
            {
                _costItem[i].Hidden = true;
            }
        }
    }
}
