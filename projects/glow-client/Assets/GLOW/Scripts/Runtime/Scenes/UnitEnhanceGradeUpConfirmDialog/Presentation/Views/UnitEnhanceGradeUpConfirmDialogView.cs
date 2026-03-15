using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.UnitEnhanceGradeUpConfirmDialog.Presentation.ViewModels;
using GLOW.Scenes.UnitEnhanceRankUpConfirmDialog.Presentation.Views;
using GLOW.Scenes.UnitLevelUpDialogView.Presentation.Views;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.UnitEnhanceGradeUpConfirmDialog.Presentation.Views
{
    public class UnitEnhanceGradeUpConfirmDialogView : UIView
    {
        [Header("ランク")]
        [SerializeField] IconGrade _beforeRank;
        [SerializeField] IconGrade _afterRank;

        [Header("所持アイテム")]
        [SerializeField] UnitEnhanceCostItemComponent _costItem;
        [SerializeField] ChildScaler _costItemChildScaler;

        [Header("ステータス")]
        [SerializeField] UnitEnhanceBaseStatusPreviewComponent _baseStatus;

        [Header("原画獲得")]
        [SerializeField] UIObject _artworkRewardRoot;
        [SerializeField] UIText _artworkRewardText;

        [Header("必殺ワザ")]
        [SerializeField] UnitEnhanceSpecialAttackPreviewComponent _specialAttackPreview;

        [Header("確認ボタン")]
        [SerializeField] UITextButton _confirmButton;

        public void Setup(UnitEnhanceGradeUpConfirmViewModel viewModel)
        {
            _costItem.Setup(viewModel.Item, viewModel.PossessionAmount);
            _beforeRank.SetGrade(viewModel.BeforeGrade.Value);
            _afterRank.SetGrade(viewModel.AfterGrade.Value);

            _baseStatus.Hidden = viewModel.RoleType == CharacterUnitRoleType.Special;
            _baseStatus.SetupHP(viewModel.BeforeHp, viewModel.AfterHp);
            _baseStatus.SetupAttackPower(viewModel.BeforeAttackPower, viewModel.AfterAttackPower);

            SetArtworkReward(viewModel.CharacterName);

            _confirmButton.interactable = viewModel.EnableConfirm.IsEnable();
        }

        public void SetSpecialAttackPreview(
            SpecialAttackName specialAttackName,
            SpecialAttackInfoDescription specialAttackDescription)
        {
            _specialAttackPreview.Setup(specialAttackName, specialAttackDescription);
        }

        public void PlayCostItemAppearanceAnimation()
        {
            _costItemChildScaler.Play();
        }

        void SetArtworkReward(CharacterName characterName)
        {
            _artworkRewardRoot.IsVisible = !characterName.IsEmpty();
            _artworkRewardText.SetText("「{0}」の原画ゲット!!", characterName.Value);
        }
    }
}
