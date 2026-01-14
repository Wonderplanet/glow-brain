using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.CustomCarousel;
using GLOW.Scenes.Home.Presentation.ViewModels;
using UnityEngine;
using WPFramework.Presentation.Views;

namespace GLOW.Scenes.Home.Presentation.Views
{
    public enum CarouselDirection
    {
        Right,
        Left
    }
    public class HomeMainStageSelectControl : IGlowCustomCarouselViewDataSource
    , IGlowCustomCarouselViewDelegate
    {
        IReadOnlyList<HomeMainStageViewModel> _data;
        readonly GlowCustomInfiniteCarouselView _carouselView;
        readonly HomeMainQuestView _view;
        readonly Action<HomeMainStageViewModel, bool, bool> _onSelect;
        readonly IHomeMainViewDelegate _viewDelegate;
        HomeMainStageViewModel _selectedStageViewModel;

        public void SetData(
            IReadOnlyList<HomeMainStageViewModel> data,
            HomeMainStageViewModel selectedStageViewModel){
            _data = data;
            _selectedStageViewModel = selectedStageViewModel;
        }
        public HomeMainStageSelectControl(
            HomeMainQuestView view,
            GlowCustomInfiniteCarouselView carouselView,
            IReadOnlyList<HomeMainStageViewModel> data,
            HomeMainStageViewModel selectedStageViewModel,
            Action<HomeMainStageViewModel ,bool,bool> onSelect,
            IHomeMainViewDelegate viewDelegate)
        {
            _view = view;
            _carouselView = carouselView;
            _data = data;
            _selectedStageViewModel = selectedStageViewModel;
            _onSelect = onSelect;
            _viewDelegate = viewDelegate;
        }

        void IGlowCustomCarouselViewDelegate.AccessoryButtonTappedForRowWith(
            GlowCustomInfiniteCarouselView carouselView,
            int indexPath,
            object identifier)
        {
            //NOTE: HomeMainStageSelectCell.Awakeでidentifierを設定
            if ((string)identifier == "info")
            {
                _viewDelegate.OnQuestInfoClicked(_data[indexPath].MstStageId);
            }
            else if ((string)identifier == "unRelease")
            {
                _viewDelegate.OnQuestUnReleasedClicked(_data[indexPath].ReleaseRequireSentence);
            }
        }

        //今はBuildするときにしか使ってない
        int IGlowCustomCarouselViewDataSource.SelectedIndex()
        {
            var target = _data
                .FirstOrDefault(d => d.MstStageId == _selectedStageViewModel.MstStageId);
            if (null == target)
            {
                target = _data.LastOrDefault(s => s.PlayableFlag.Value) ?? _data.First();
            }

            return target == null ? 0 : _data.IndexOf(target);
        }

        GlowCustomInfiniteCarouselCell IGlowCustomCarouselViewDataSource.CellForItemAtIndex(int index)
        {
            var cell = _carouselView.DequeueReusableCell<HomeMainStageSelectCell>();
            var model = _data[index];
            cell.IsReleased(model.PlayableFlag);
            cell.SetStatus(model.StageClearStatus);
            cell.SetArtworkFragmentStatus(model.IsShowArtworkFragmentIcon);
            cell.SetRewardIconStatus(model.IsShowRewardCompleteIcon);
            cell.StageNumber = model.StageNumber.Value;
            // Lock(=Icon非表示)状態でLoadSpriteWithFadeIfNotLoaded呼ぶと、Load処理がスタックしクエスト変更後に想定しないアイコンになる
            // ので、選択可能のときだけIcon設定するようにする
            if(model.PlayableFlag) cell.StageImageAssetPath = model.StageIconAssetPath.Value;
            cell.SetOnChangeCenterIndex(centerIndex=> cell.OnUpdateButtonStatus(centerIndex));

            return cell;
        }

        int IGlowCustomCarouselViewDataSource.NumberOfItems()
        {
            return NumberOfItems();
        }

        int NumberOfItems()
        {
            return _data?.Count ?? 0;
        }
        void IInfiniteCarouselViewDelegate.DidLayoutCell(InfiniteCarouselCell cell, int index)
        {
            // NOTE: 画面中央からの距離に応じてスケールを変更する
            var rectTransform = _view.CarouselView.RectTransform;
            var position = cell.RectTransform.localPosition;
            var distance = Mathf.Abs(position.x);//原点からの距離...pos.x=0を原点としたときの距離
            var maxDistance = rectTransform.rect.width / _view.MaxDistanceMargin;//最大距離...cell幅の_view.MaxDistanceMargin倍の距離
            distance = maxDistance < distance ? maxDistance : distance;

            var cellTransform = (RectTransform)cell.transform;
            var cellScale = maxDistance / (maxDistance + (distance+_view.CellSizeMargin));
            var cellLocalScale = new Vector2(cellScale, cellScale);
            // NOTE: 無効値はスケールを変更しない
            if (float.IsNaN(cellLocalScale.x) || float.IsNaN(cellLocalScale.y))
            {
                return;
            }

            cellTransform.localScale = cellLocalScale;
        }

        void IInfiniteCarouselViewDelegate.DidSelectItemAtIndex(int index)
        {
            var model = _data?[index];
            var shouldShowLeftButton = 0 < index;
            var shouldShowRightButton = index < _data.Count - 1;
            _onSelect?.Invoke(model,shouldShowLeftButton,shouldShowRightButton);
        }

        public bool OnMoveButtonIfNeed(CarouselDirection direction)
        {
            if (direction == CarouselDirection.Right)
            {
                //indexを見るのに対して、NumberOfItemsはCountであることに注意
                return _carouselView.CurrentIndex + 1 <= (NumberOfItems()-1);
            }
            else
            {
                return 0 <= _carouselView.CurrentIndex - 1;
            }
        }

        public void MoveLeft()
        {
            if (_carouselView != null)
            {
                _carouselView.MoveLeft();
            }
        }
        public void MoveRight()
        {
            if (_carouselView != null)
            {
                _carouselView.MoveRight();
            }
        }

    }
}
