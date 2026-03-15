using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.ArtworkEnhance.Domain.ValueObjects;
using GLOW.Scenes.ArtworkEnhance.Presentation.Presenter.Component;
using GLOW.Scenes.ArtworkEnhance.Presentation.ViewModels;
using GLOW.Scenes.EncyclopediaArtworkDetail.Presentation.Views;
using UIKit;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.ArtworkEnhance.Presentation.View
{
    public class ArtworkEnhanceView : UIView
    {
        [Header("原画情報関連")]
        [SerializeField] EncyclopediaArtworkPageComponent _artworkPageComponent;
        [SerializeField] UIText _artworkNameText;
        [SerializeField] UIImage _seriesImage;
        [SerializeField] UnitRarityIcon _seriesRarityIcon;
        [SerializeField] IconGrade _artworkGradeIcon;
        [SerializeField] UIObject _acquisitionRouteButton;

        [Header("グレードアップ関連")]
        [SerializeField] Button _artworkUpGradeButton;
        [SerializeField] GameObject _upGradeButtonGrayOutObject;
        [SerializeField] GameObject _dotIndicatorObject;
        [SerializeField] GameObject _artworkGradeUpRequirementsObject;
        [SerializeField] List<ArtworkGradeUpRequiredIconComponent> _upGradeItemIcons;

        [Header("タブ")]
        [SerializeField] UIToggleableComponentGroup _artworkDescriptionToggleableGroup;
        [SerializeField] UIText _tabText;

        [Header("原画効果")]
        [SerializeField] UIText _artworkEffectDescriptionText;  // 原画効果
        [SerializeField] GameObject _artworkEffectDescriptionTabObject;

        [Header("原画説明")]
        [SerializeField] UIText _artworkDescriptionText;        // 原画説明
        [SerializeField] GameObject _artworkDescriptionTabObject;
        [SerializeField] GameObject _artworkDescriptionGrayOutObject;

        [Header("原画アニメーション")]
        [SerializeField] ChildScaler _childScaler;

        [Header("切り替え用の左右ボタン")]
        [SerializeField] UIObject _leftArrowButtonObject;
        [SerializeField] UIObject _rightArrowButtonObject;

        public EncyclopediaArtworkPageComponent ArtworkPageComponent => _artworkPageComponent;

        const string _artworkEffectDescriptionTabKey = "ArtworkEffect";
        const string _artworkDescriptionTabKey = "Description";

        public string ArtworkEffectTabKey => _artworkEffectDescriptionTabKey;
        public string ArtworkDescriptionTabKey => _artworkDescriptionTabKey;

        public void Setup(
            ArtworkEnhanceViewModel viewModel,
            Action<PlayerResourceIconViewModel> onIconTapped)
        {
            SetUpArtworkName(viewModel.Name);
            SetUpSeriesImage(viewModel.SeriesLogoImagePath);
            SetUpArtworkEffectDescription(viewModel.EffectDescription);
            SetUpArtworkDescription(viewModel.ArtworkDescription);
            SetUpUpGradeItemIcons(viewModel.GradeUpRequiredIconViewModels, viewModel.GradeUpIconViewModels, onIconTapped);
            SetUpArtworkDescriptionGrayOutObject(viewModel.IsArtworkCompleted);
            SetSeriesRarity(viewModel.Rarity);
            SetArtworkDescriptionTab(_artworkEffectDescriptionTabKey);
            SetUpArtworkGrade(viewModel.GradeLevel);
            SetUpGradeUpButton(
                viewModel.IsArtworkCompleted,
                viewModel.IsGradeUpAvailable,
                viewModel.IsGradeMaxLimit);
            SetUpUpGradeIndicator(viewModel.IsGradeUpAvailable, viewModel.IsArtworkCompleted);
            SetUpArtworkAcquisitionRouteButton(viewModel.IsAcquisitionRouteExists);
        }

        public void PlayArtworkAnimation()
        {
            _childScaler.Play();
        }

        public void SetArtworkDescriptionTab(string key)
        {
            _artworkDescriptionToggleableGroup.SetToggleOn(key);

            var tabText = key switch
            {
                _artworkEffectDescriptionTabKey => "原画効果",
                _artworkDescriptionTabKey => "あらすじ",
                _ => ""
            };

            _tabText.SetText(tabText);

            _artworkEffectDescriptionTabObject.SetActive(key == _artworkEffectDescriptionTabKey);
            _artworkDescriptionTabObject.SetActive(key == _artworkDescriptionTabKey);
        }

        public void SetArrowButtonsVisible(bool visible)
        {
            _leftArrowButtonObject.IsVisible = visible;
            _rightArrowButtonObject.IsVisible = visible;
        }

        void SetSeriesRarity(Rarity rarity)
        {
            _seriesRarityIcon.Setup(rarity);
        }

        void SetUpGradeUpButton(
            ArtworkCompletedFlag completedFlag,
            ArtworkGradeUpAvailableFlag gradeUpAvailableFlag,
            ArtworkGradeMaxLimitFlag gradeMaxLimitFlag)
        {
            bool isInactive;
            if (!completedFlag)
            {
                // 未完成の場合はグレードアップ不可
                isInactive = true;
            }
            else if (gradeMaxLimitFlag)
            {
                // グレード上限に達している場合はグレードアップ不可
                isInactive = true;
            }
            else
            {
                // それ以外はグレードアップ可能
                isInactive = false;
            }

            _dotIndicatorObject.SetActive(gradeUpAvailableFlag && !isInactive);
            _artworkGradeUpRequirementsObject.SetActive(!isInactive);
            _upGradeButtonGrayOutObject.SetActive(isInactive);
        }

        void SetUpUpGradeIndicator(
            ArtworkGradeUpAvailableFlag availableFlag,
            ArtworkCompletedFlag completedFlag)
        {
            _dotIndicatorObject.SetActive(availableFlag && completedFlag);
        }

        void SetUpArtworkGrade(ArtworkGradeLevel artworkGradeLevel)
        {
            _artworkGradeIcon.SetGrade(artworkGradeLevel.Value);
        }

        void SetUpArtworkName(ArtworkName artworkName)
        {
            _artworkNameText.SetText(artworkName.Value);
        }

        void SetUpSeriesImage(SeriesLogoImagePath seriesLogoImagePath)
        {
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_seriesImage.Image, seriesLogoImagePath.Value);
        }

        void SetUpArtworkEffectDescription(ArtworkEffectDescription effectDescription)
        {
            _artworkEffectDescriptionText.SetText(effectDescription.Value);
        }

        void SetUpArtworkDescription(ArtworkDescription artworkDescription)
        {
            _artworkDescriptionText.SetText(artworkDescription.Value);
        }

        void SetUpArtworkDescriptionGrayOutObject(ArtworkCompletedFlag artworkCompletedFlag)
        {
            _artworkDescriptionGrayOutObject.SetActive(!artworkCompletedFlag);
        }

        void SetUpUpGradeItemIcons(
            IReadOnlyList<ArtworkGradeUpRequiredIconViewModel> groupedIconViewModels,
            IReadOnlyList<PlayerResourceIconViewModel> iconViewModels,
            Action<PlayerResourceIconViewModel> onIconTapped)
        {
            // 最初にすべて非表示にする
            foreach (var icon in _upGradeItemIcons)
            {
                icon.IsVisible = false;
            }

            for (int i = 0; i < iconViewModels.Count; i++)
            {
                var component = _upGradeItemIcons[i];
                var viewModel = groupedIconViewModels[i];
                var icon = iconViewModels[i];
                component.IsVisible = true;
                component.Setup(viewModel, () => { onIconTapped(icon); });
            }
        }

        void SetUpArtworkAcquisitionRouteButton(ArtworkAcquisitionRouteExistsFlag acquisitionRouteExistsFlag)
        {
            _acquisitionRouteButton.IsVisible = acquisitionRouteExistsFlag;
        }
    }
}
