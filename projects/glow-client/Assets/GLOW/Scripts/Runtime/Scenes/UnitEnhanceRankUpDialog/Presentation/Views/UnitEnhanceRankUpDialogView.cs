using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.UnitEnhance.Presentation.Views.Components;
using GLOW.Scenes.UnitEnhanceRankUpDialog.Presentation.ViewModels;
using GLOW.Scenes.UnitLevelUpDialogView.Presentation.Views;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.UnitEnhanceRankUpDialog.Presentation.Views
{
    public class UnitEnhanceRankUpDialogView : UIView
    {
        [SerializeField] UIImage _unitImage;
        [SerializeField] UIText _beforeRank;
        [SerializeField] UIText _afterRank;
        [SerializeField] UnitEnhanceBaseStatusPreviewComponent _baseStatusPreview;
        [SerializeField] UnitEnhanceSpecialStatusPreviewComponent _specialStatusPreview;
        [SerializeField] UnitEnhanceAbilityComponent _abilityComponent;
        [SerializeField] UIObject _closeButton;
        [SerializeField] UIText _closeText;

        public void Setup(UnitEnhanceRankUpDialogViewModel viewModel)
        {
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_unitImage.Image, viewModel.AssetPath.Value);

            _beforeRank.SetText(viewModel.BeforeLimitLevel.ToStringWithPrefixLv());
            _afterRank.SetText(viewModel.AfterLimitLevel.ToStringWithPrefixLv());

            var isSpecial = viewModel.RoleType == CharacterUnitRoleType.Special;
            _baseStatusPreview.Hidden = isSpecial;
            _specialStatusPreview.Hidden = !isSpecial;
            _baseStatusPreview.SetupHP(viewModel.BeforeHP, viewModel.AfterHP);
            _baseStatusPreview.SetupAttackPower(viewModel.BeforeAttackPower, viewModel.AfterAttackPower);
            _specialStatusPreview.Setup(viewModel.BeforeAttackPower, viewModel.AfterAttackPower);
            _abilityComponent.Setup(viewModel.NewlyAbilityViewModels);
        }

        public void AnimationEnded()
        {
            _closeButton.Hidden = false;
            _closeText.Hidden = false;
        }
    }
}
