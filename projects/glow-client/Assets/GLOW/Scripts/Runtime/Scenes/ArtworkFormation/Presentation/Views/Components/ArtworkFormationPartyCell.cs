using System;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.ArtworkFormation.Presentation.ViewModels;
using UnityEngine;
using UnityEngine.UI;
using WonderPlanet.ResourceManagement;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.ArtworkFormation.Presentation.Views.Components
{
    public class ArtworkFormationPartyCell : UIObject
    {
        [SerializeField] UIImage _artworkImage;
        [SerializeField] Button _button;
        [SerializeField] IconRarityImage _rarityImage;
        [SerializeField] IconGrade _gradeImage;

        bool _isAssigned;

        public void SetUp(ArtworkFormationPartyCellViewModel viewModel)
        {
            IsVisible = true;

            UISpriteUtil.LoadSpriteWithFade(_artworkImage.Image, viewModel.AssetPath.Value);
            _rarityImage.Setup(viewModel.Rarity);
            _gradeImage.SetGrade(viewModel.Grade);
        }

        public void SetOnClickListener(Action onClickAction)
        {
            _button.onClick.RemoveAllListeners();
            if (onClickAction != null)
            {
                _button.onClick.AddListener(() => onClickAction());
            }
        }

        public void Clear()
        {
            // SpriteLoaderをクリアして_retainedSpriteを解放
            var spriteLoader = _artworkImage.Image.gameObject.GetComponent<SpriteLoader>();
            if (spriteLoader != null)
            {
                spriteLoader.Clear();
            }

            // 画像を解放
            _artworkImage.Image.sprite = null;

            // ボタンのリスナーを解除
            _button.onClick.RemoveAllListeners();

            // 非表示にする
            IsVisible = false;
        }
    }
}

