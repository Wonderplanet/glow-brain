using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.ArtworkEnhance.Domain.ValueObjects;
using GLOW.Scenes.ArtworkEnhance.Presentation.Presenter.Component;
using GLOW.Scenes.ArtworkEnhance.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.ArtworkEnhance.Presentation.View
{
    public class ArtworkGradeUpConfirmView : UIView
    {
        [Header("グレード関連")]
        [SerializeField] IconGrade _currentGrade;
        [SerializeField] IconGrade _nextGrade;
        [SerializeField] UITextButton _gradeUpButton;

        [Header("額縁追加")]
        [SerializeField] GameObject _artworkFrameObject;
        [SerializeField] UIText _artworkFrameText;

        [Header("必要アイテム関連")]
        [SerializeField] ChildScaler _artworkUpGradeItemIconsChildScaler;
        [SerializeField] List<ArtworkGradeUpCostItemComponent> _requiredUpGradeItemIconComponent;

        [Header("説明関連")]
        [SerializeField] UIText _artworkEffectDescriptionText;

        public UITextButton GradeUpButton => _gradeUpButton;

        const string ArtworkFrameTextFormat = "「{0}」の原画の額縁GET!!";

        public void Setup(
            ArtworkUpGradeConfirmViewModel viewModel,
            Action<PlayerResourceIconViewModel> onIconTapped = null)
        {
            _artworkUpGradeItemIconsChildScaler.Play();

            SetUpUpGradeItemIcons(
                viewModel.RequiredEnhanceItemViewModels,
                onIconTapped);
            SetUpGrade(viewModel.CurrentGradeLevel, viewModel.NextGradeLevel);
            SetUpArtworkFrameObject(viewModel.IsGradeMaxLimit, viewModel.ArtworkName);
            SetUpArtworkEffectDescription(viewModel.EffectDescription);
        }

        void SetUpUpGradeItemIcons(
            IReadOnlyList<RequiredEnhanceItemViewModel> iconViewModels,
            Action<PlayerResourceIconViewModel> onIconTapped)
        {
            // いったん全て非表示にする
            foreach (var component in _requiredUpGradeItemIconComponent)
            {
                component.IsVisible = false;
            }

            for (int i = 0; i < iconViewModels.Count; i++)
            {
                var iconViewModel = iconViewModels[i];
                _requiredUpGradeItemIconComponent[i].IsVisible = true;
                _requiredUpGradeItemIconComponent[i].Setup(
                    iconViewModel.RequiredUpGradeItemIconViewModel,
                    iconViewModel.PossessionAmount,
                    iconViewModel.ConsumeAmount,
                    () => onIconTapped?.Invoke(iconViewModel.RequiredUpGradeItemIconViewModel));
            }
        }

        void SetUpGrade(ArtworkGradeLevel currentGradeLevel, ArtworkGradeLevel nextGradeLevel)
        {
            _currentGrade.SetGrade(currentGradeLevel.Value);
            _nextGrade.SetGrade(nextGradeLevel.Value);
        }

        void SetUpArtworkFrameObject(ArtworkGradeMaxLimitFlag gradeMaxLimitFlag, ArtworkName artworkName)
        {
            _artworkFrameObject.SetActive(gradeMaxLimitFlag);
            _artworkFrameText.SetText(ArtworkFrameTextFormat, artworkName.Value);
        }

        void SetUpArtworkEffectDescription(ArtworkEffectDescription effectDescription)
        {
            _artworkEffectDescriptionText.SetText(effectDescription.Value);
        }
    }
}
