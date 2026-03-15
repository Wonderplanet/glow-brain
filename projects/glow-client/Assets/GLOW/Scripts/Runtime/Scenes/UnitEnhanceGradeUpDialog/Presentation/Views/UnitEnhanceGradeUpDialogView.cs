using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.UnitEnhanceGradeUpDialog.Presentation.ViewModels;
using GLOW.Scenes.UnitLevelUpDialogView.Presentation.Views;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.UnitEnhanceGradeUpDialog.Presentation.Views
{
    public class UnitEnhanceGradeUpDialogView : UIView
    {
        [SerializeField] UIImage _unitImage;
        [SerializeField] IconGrade _beforeGrade;
        [SerializeField] IconGrade _afterGrade;
        [SerializeField] UnitEnhanceBaseStatusPreviewComponent _baseStatus;
        [SerializeField] UnitEnhanceSpecialAttackPreviewComponent _specialAttackPreview;
        [SerializeField] UIObject _closeButton;
        [SerializeField] UIText _closeText;
        [SerializeField] UIObject _artworkPlateRoot;
        [SerializeField] UIText _artworkNameText;

        public void Setup(UnitEnhanceGradeUpDialogViewModel viewModel)
        {
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_unitImage.Image, viewModel.AssetPath.Value);

            _beforeGrade.SetGrade(viewModel.BeforeGrade.Value);
            _afterGrade.SetGrade(viewModel.AfterGrade.Value);

            _baseStatus.Hidden = viewModel.RoleType == CharacterUnitRoleType.Special;
            _baseStatus.SetupHP(viewModel.BeforeHP, viewModel.AfterHP);
            _baseStatus.SetupAttackPower(viewModel.BeforeAttackPower, viewModel.AfterAttackPower);
            SetArtworkReward(viewModel.CharacterName);
        }

        public void SetSpecialAttackPreview(
            SpecialAttackName specialAttackName,
            SpecialAttackInfoDescription specialAttackDescription)
        {
            _specialAttackPreview.Setup(specialAttackName, specialAttackDescription);
        }

        public void AnimationEnded()
        {
            _closeButton.Hidden = false;
            _closeText.Hidden = false;
        }

        void SetArtworkReward(CharacterName characterName)
        {
            _artworkPlateRoot.IsVisible = !characterName.IsEmpty();
            _artworkNameText.SetText("「{0}」の原画ゲット!!", characterName.Value);
        }
    }
}
