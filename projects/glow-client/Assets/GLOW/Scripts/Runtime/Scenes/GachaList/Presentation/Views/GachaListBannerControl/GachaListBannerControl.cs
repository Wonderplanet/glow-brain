using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.CustomCarousel;
using GLOW.Scenes.GachaList.Presentation.ViewModels;
using GLOW.Scenes.Home.Presentation.Views;
using UnityEngine;
using Wonderplanet.UIHaptics.Presentation;
using WPFramework.Presentation.Views;

namespace GLOW.Scenes.GachaList.Presentation.Views.GachaListBannerControl
{
    public interface IGachaListBannerControlInitializer
    {
        void InitializeView(IHapticsPresenter hapticsPresenter);
    }

    public interface IGachaListBannerControlDelegate
    {
        void UpdateViewModel(MasterDataId initialShowOprGachaId, IReadOnlyList<GachaFooterBannerViewModel> gachaFooterBannerViewModels);
        void MoveLeft();
        void MoveRight();
    }
    public class GachaListBannerControl :
        IGachaListBannerControlInitializer,
        IGachaListBannerControlDelegate,
        IGlowCustomCarouselViewDelegate,
        IGlowCustomCarouselViewDataSource
    {
        readonly GlowCustomInfiniteCarouselView _carouselView;
        MasterDataId _initialShowOprGachaId;
        IReadOnlyList<GachaFooterBannerViewModel> _gachaFooterBannerViewModels;

        readonly Action<GachaFooterBannerViewModel> _onSelect;

        // バナー間隔調整向け
        readonly float _maxDistanceMargin;
        readonly float _cellSizeMargin;

        public GachaListBannerControl(
            GlowCustomInfiniteCarouselView carouselView,
            MasterDataId initialShowOprGachaId,
            IReadOnlyList<GachaFooterBannerViewModel> gachaFooterBannerViewModels,
            Action<GachaFooterBannerViewModel> onSelect,
            float maxDistanceMargin,
            float cellSizeMargin)
        {
            _carouselView = carouselView;
            _initialShowOprGachaId = initialShowOprGachaId;
            _gachaFooterBannerViewModels = gachaFooterBannerViewModels;
            _onSelect = onSelect;
            _maxDistanceMargin = maxDistanceMargin;
            _cellSizeMargin = cellSizeMargin;
        }

        void IGachaListBannerControlInitializer.InitializeView(IHapticsPresenter hapticsPresenter)
        {
            _carouselView.HapticsPresenter = hapticsPresenter;
            _carouselView.ViewDelegate = this;
            _carouselView.DataSource = this;
        }

        void IGachaListBannerControlDelegate.UpdateViewModel(MasterDataId initialShowOprGachaId, IReadOnlyList<GachaFooterBannerViewModel> gachaFooterBannerViewModels)
        {
            _initialShowOprGachaId = initialShowOprGachaId;
            _gachaFooterBannerViewModels = gachaFooterBannerViewModels;
            _carouselView.ReloadData();
        }

        void IGachaListBannerControlDelegate.MoveLeft()
        {
            if (_carouselView != null)
            {
                _carouselView.MoveLeft();
            }
        }
        void IGachaListBannerControlDelegate.MoveRight()
        {
            if (_carouselView != null)
            {
                _carouselView.MoveRight();
            }
        }

        void IInfiniteCarouselViewDelegate.DidSelectItemAtIndex(int index)
        {
            var model = _gachaFooterBannerViewModels[index];
            _onSelect?.Invoke(model);
        }

        void IInfiniteCarouselViewDelegate.DidLayoutCell(InfiniteCarouselCell cell, int index)
        {
            // NOTE: 画面中央からの距離に応じてスケールを変更する
            var rectTransform = _carouselView.RectTransform;
            var position = cell.RectTransform.localPosition;
            var distance = Mathf.Abs(position.x); //原点からの距離...pos.x=0を原点としたときの距離
            var maxDistance = rectTransform.rect.width / _maxDistanceMargin; //最大距離...cell幅の_view.MaxDistanceMargin倍の距離
            distance = maxDistance < distance ? maxDistance : distance;

            var cellTransform = (RectTransform)cell.transform;
            var cellScale = maxDistance / (maxDistance + (distance + _cellSizeMargin));
            var cellLocalScale = new Vector2(cellScale, cellScale);
            // NOTE: 無効値はスケールを変更しない
            if (float.IsNaN(cellLocalScale.x) || float.IsNaN(cellLocalScale.y))
            {
                return;
            }
            cellTransform.localScale = cellLocalScale;

            //------
            // 中央に近いほど上に上げる（距離が遠いほど下がる）
            var normalizedDistance = distance / maxDistance; // 0〜1の範囲に正規化
            var yOffset = (1f - normalizedDistance) * 80f; // 中央で80、端で0になる
            cellTransform.localPosition = new Vector3(
                position.x,
                yOffset,
                position.z
            );
        }

        void IGlowCustomCarouselViewDelegate.AccessoryButtonTappedForRowWith(GlowCustomInfiniteCarouselView carouselView, int indexPath, object identifier)
        {
            // no use.
        }

        // Rebuildに引っ掛けてあるからUpdateのタイミングでinitialOprGachaId変えれば変更してくれる
        int IGlowCustomCarouselViewDataSource.SelectedIndex()
        {
            if (_gachaFooterBannerViewModels == null) return 0;

            var target = _gachaFooterBannerViewModels
                .First(f => f.OprGachaId == _initialShowOprGachaId);

            return _gachaFooterBannerViewModels.IndexOf(target);
        }

        GlowCustomInfiniteCarouselCell IGlowCustomCarouselViewDataSource.CellForItemAtIndex(int index)
        {
            var cell = _carouselView.DequeueReusableCell<GachaFooterBannerCell>();
            var model = _gachaFooterBannerViewModels[index];
            if (model == null)
            {
                return cell;
            }

            cell.Setup(model);
            return cell;
        }

        int IGlowCustomCarouselViewDataSource.NumberOfItems()
        {
            return NumberOfItems();
        }

        int NumberOfItems()
        {
            return _gachaFooterBannerViewModels?.Count ?? 0;
        }
    }
}
