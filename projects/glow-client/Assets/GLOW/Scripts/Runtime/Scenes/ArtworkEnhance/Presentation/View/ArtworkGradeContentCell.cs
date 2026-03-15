using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.ArtworkEnhance.Domain.ValueObjects;
using GLOW.Scenes.ArtworkEnhance.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.ArtworkEnhance.Presentation.View
{
    public class ArtworkGradeContentCell : UICollectionViewCell
    {
        [SerializeField] UIText _gradeUpRequiredGradeText;
        [SerializeField] UIText _targetGradeText;
        [SerializeField] UIText _openGradeText;
        [SerializeField] List<PlayerResourceIconButtonComponent> _gradeUpRequiredItemIconComponents;
        [SerializeField] GameObject _grayOutObject;
        [SerializeField] GameObject _artworkFrameObject;
        [SerializeField] UIText _artworkFrameText;

        const string TargetGradeTextFormat = "グレード{0}";
        const string OpenGradeTextFormat = "グレード{0}まで開放";
        const string ArtworkFrameTextFormat = "「{0}」の原画の額縁GET!!";

        public void Setup(
            ArtworkGradeContentCellViewModel viewModel,
            Action<PlayerResourceIconViewModel> onIconTapped = null)
        {
            SetUpTargetGradeText(viewModel.TargetGradeLevel);
            SetUpRequiredGradeText(viewModel.RequiredGradeLevel);
            SetUpRequiredItemIcons(viewModel.RequiredItemIconViewModels, onIconTapped);
            SetUpOpenGradeText(viewModel.IsGradeReleased, viewModel.TargetGradeLevel);
            SetUpArtworkFrame(viewModel.IsGradeMaxLimit, viewModel.ArtworkName);
        }

        void SetUpRequiredItemIcons(
            IReadOnlyList<PlayerResourceIconViewModel> iconViewModels,
            Action<PlayerResourceIconViewModel> onIconTapped = null)
        {
            foreach(var icon in _gradeUpRequiredItemIconComponents)
            {
                icon.IsVisible = false;
            }

            for (int i = 0; i < iconViewModels.Count; i++)
            {
                var iconViewModel = iconViewModels[i];
                var iconComponent = _gradeUpRequiredItemIconComponents[i];
                iconComponent.IsVisible = true;
                iconComponent.Setup(iconViewModel, () => onIconTapped?.Invoke(iconViewModel));
            }
        }

        void SetUpTargetGradeText(ArtworkGradeLevel targetGradeLevel)
        {
            _targetGradeText.SetText(TargetGradeTextFormat, targetGradeLevel.Value);
        }

        void SetUpRequiredGradeText(ArtworkGradeLevel requiredGradeLevel)
        {
            _gradeUpRequiredGradeText.SetText(requiredGradeLevel.Value.ToString());
        }

        void SetUpOpenGradeText(ArtworkGradeReleasedFlag isGradeReleased, ArtworkGradeLevel targetGradeLevel)
        {
            _grayOutObject.SetActive(isGradeReleased);
            _openGradeText.SetText(OpenGradeTextFormat, targetGradeLevel.Value);
        }

        void SetUpArtworkFrame(ArtworkGradeMaxLimitFlag isGradeMaxLimit, ArtworkName artworkName)
        {
            _artworkFrameObject.SetActive(isGradeMaxLimit);
            _artworkFrameText.SetText(ArtworkFrameTextFormat, artworkName.Value);
        }
    }
}
