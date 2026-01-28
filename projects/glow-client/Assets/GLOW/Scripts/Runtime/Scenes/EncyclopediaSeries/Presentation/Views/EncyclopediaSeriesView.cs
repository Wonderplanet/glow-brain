using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.EncyclopediaSeries.Domain.ValueObjects;
using GLOW.Scenes.EncyclopediaSeries.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.EncyclopediaSeries.Presentation.Views
{
    /// <summary>
    /// 91_図鑑
    /// 　91-2_作品別TOP画面
    /// 　　91-2-1-1_作品別TOP画面タブ
    /// 　　91-2-2_作品別キャラ一覧TOP画面
    /// 　　91-2-3_作品別コレクションTOP画面
    /// 　　　91-2-3-1_作品別原画一覧
    /// 　　　91-2-3-2_作品別エンブレム一覧
    /// </summary>
    public class EncyclopediaSeriesView : UIView
    {
        [Header("タブ")]
        [SerializeField] UIToggleableComponentGroup _toggleableComponentGroup;

        [SerializeField] UIObject _unitTabBadge;
        [SerializeField] UIObject _collectionTabBadge;
        [Header("タブ内画面")]
        [SerializeField] EncyclopediaSeriesUnitListComponent _listComponent;
        [SerializeField] EncyclopediaSeriesCollectionListComponent _collectionListComponent;
        [Header("コンテンツ情報")]
        [SerializeField] SeriesLogoComponent _seriesLogo;
        [SerializeField] UIImage _questThumbnailImage;

        [Header("背景画像")]
        [SerializeField] UIImage _questThumbnailBackGroundImage;
        [SerializeField] UILightBlurTextureComponent _uiLightBlurTextureComponent;

        public void SetupUnitList(
            EncyclopediaSeriesUnitListViewModel viewModel,
            Action<MasterDataId, EncyclopediaUnlockFlag> onSelectPlayerUnitAction,
            Action<MasterDataId, EncyclopediaUnlockFlag> onSelectEnemyUnitAction)
        {
            _listComponent.Setup(viewModel.PlayerUnits, viewModel.EnemyUnits, onSelectPlayerUnitAction, onSelectEnemyUnitAction);
        }

        public void SetupCollectionList(EncyclopediaSeriesCollectionListViewModel viewModel,
            Action<MasterDataId> onSelectArtworkAction,
            Action<MasterDataId> onSelectEmblemAction)
        {
            _collectionListComponent.Setup(viewModel, onSelectArtworkAction, onSelectEmblemAction);
        }

        public void SetContentInfo(SeriesLogoImagePath logoImagePath, SeriesIconImagePath seriesIconImagePath)
        {
            _seriesLogo.Setup(logoImagePath);
            SetQuestThumbnail(seriesIconImagePath);
        }

        public void SetUnitBadge(NotificationBadge badge)
        {
            _unitTabBadge.Hidden = !badge;
        }

        public void SetCollectionBadge(NotificationBadge badge)
        {
            _collectionTabBadge.Hidden = !badge;

        }
        void SetQuestThumbnail(SeriesIconImagePath path)
        {
            //前面
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(
                _questThumbnailImage.Image,
                path.Value,
                () =>
                {
                    // pngのpivotに合わせて位置を調整する
                    if (!_questThumbnailImage)
                    {
                        return;
                    }
                    var updateRectTransform = GetQuestImagePosition(_questThumbnailImage);
                    var rect = (RectTransform)_questThumbnailImage.transform;
                    rect.pivot = updateRectTransform.pivot;
                    rect.anchoredPosition = updateRectTransform.position;
                });

            //背面
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(
                _questThumbnailBackGroundImage.Image,
                path.Value,
                () =>
                {
                    //NOTE: RawImageを使いたいがUISpriteUtilなどからAssetBundle化された画像を直接RawImageで取得する手段がないため
                    //一旦Imageで取得してからRawImage(_uiLightBlurTextureComponent)に設定をする。
                    //_uiLightBlurTextureComponentは設定後表示し、仲介のImageは非表示にする。
                    if (!_uiLightBlurTextureComponent || !_questThumbnailBackGroundImage)
                    {
                        return;
                    }
                    _uiLightBlurTextureComponent.Hidden = false;
                    _uiLightBlurTextureComponent.SetTexture(_questThumbnailBackGroundImage.Image.sprite.texture);
                    var updateRectTransform = GetQuestImagePosition(_questThumbnailBackGroundImage);
                    var rect = (RectTransform)_uiLightBlurTextureComponent.transform;
                    rect.pivot = updateRectTransform.pivot;
                    rect.anchoredPosition = updateRectTransform.position;

                    _questThumbnailBackGroundImage.Hidden = true;
                });
        }
        (Vector2 pivot, Vector2 position) GetQuestImagePosition(UIImage uiImage)
        {
            var rectTransform = (RectTransform)uiImage.transform;
            var pivot = uiImage.Image.sprite.pivot;
            var spriteSize = uiImage.Image.sprite.rect.size;
            var normalizedPivot = new Vector2(pivot.x / spriteSize.x, pivot.y / spriteSize.y);

            rectTransform.pivot = new Vector2(rectTransform.pivot.x, normalizedPivot.y);

            var resultPivot = new Vector2(rectTransform.pivot.x, normalizedPivot.y);
            var resultPosition = new Vector2(rectTransform.anchoredPosition.x, 0);
            return (resultPivot, resultPosition);
        }

        public void ShowCharacterList()
        {
            _listComponent.Hidden = false;
            _collectionListComponent.Hidden = true;
            _toggleableComponentGroup.SetToggleOn("character");
            _listComponent.PlayCellAppearanceAnimation();
        }

        public void ShowCollectionList()
        {
            _listComponent.Hidden = true;
            _collectionListComponent.Hidden = false;
            _toggleableComponentGroup.SetToggleOn("collection");
            _collectionListComponent.PlayCellAppearanceAnimation();
        }
    }
}
