using System;
using System.Linq;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.CustomCarousel;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.EventQuestTop.Presentation.ViewModels;
using GLOW.Scenes.Home.Presentation.Views;
using UnityEngine;
using WPFramework.Presentation.Views;

namespace GLOW.Scenes.EventQuestTop.Presentation.Views
{
    public class EventQuestTopStageSelectControl :
        IGlowCustomCarouselViewDataSource,
        IGlowCustomCarouselViewDelegate
    {
        Action<EventQuestTopElementViewModel, bool, bool> _onSelect;
        EventQuestTopViewModel _viewModel;
        EventQuestTopView _actualView;
        IEventQuestTopViewDelegate _viewDelegate;

        public EventQuestTopStageSelectControl(
            EventQuestTopView actualView,
            EventQuestTopViewModel viewModel,
            Action<EventQuestTopElementViewModel, bool, bool> onSelect,
            IEventQuestTopViewDelegate viewDelegate)
        {
            _actualView = actualView;
            _viewModel = viewModel;
            _onSelect = onSelect;
            _viewDelegate = viewDelegate;
        }

        int IGlowCustomCarouselViewDataSource.NumberOfItems()
        {
            return NumberOfItems();
        }

        int NumberOfItems()
        {
            return _viewModel?.Stages.Count ?? 0;
        }

        //今はBuildするときにしか使ってない
        int IGlowCustomCarouselViewDataSource.SelectedIndex()
        {
            if(_viewModel.Stages.Count <= 0) return 0;

            var target = _viewModel.Stages.FirstOrDefault(d => d.MstStageId == _viewModel.InitialSelectStageMstStageId);
            if (null == target)
            {
                target = _viewModel.Stages.LastOrDefault(s => s.StageReleaseStatus.IsReleased)
                         ?? _viewModel.Stages.First();
            }

            return target == null ? 0 : _viewModel.Stages.IndexOf(target);
        }

        GlowCustomInfiniteCarouselCell IGlowCustomCarouselViewDataSource.CellForItemAtIndex(int index)
        {
            var model = _viewModel.Stages[index];
            var cell = _actualView.DequeueReusableCell();
            cell.SetUpCell(model);
            cell.SetOnChangeCenterIndex(centerIndex=> cell.OnUpdateButtonStatus(centerIndex));

            return cell;
        }

        void IInfiniteCarouselViewDelegate.DidSelectItemAtIndex(int index)
        {
            var model = _viewModel?.Stages[index];
            var shouldShowLeftButton = 0 < index;
            var shouldShowRightButton = index < _viewModel?.Stages.Count - 1;
            // スタミナ消費テキストとか各種画面更新とか出す
            _onSelect?.Invoke(model, shouldShowLeftButton, shouldShowRightButton);
        }

        void IInfiniteCarouselViewDelegate.DidLayoutCell(InfiniteCarouselCell cell, int index)
        {
            // NOTE: 画面中央からの距離に応じてスケールを変更する
            var rectTransform = _actualView.CarouselViewRect;
            var position = cell.RectTransform.localPosition;
            var distance = Mathf.Abs(position.x); //原点からの距離...pos.x=0を原点としたときの距離
            var maxDistance = rectTransform.rect.width / _actualView.MaxDistanceMargin; //最大距離...cell幅の_view.MaxDistanceMargin倍の距離
            distance = maxDistance < distance ? maxDistance : distance;

            var cellTransform = (RectTransform)cell.transform;
            var cellScale = maxDistance / (maxDistance + (distance + _actualView.CellSizeMargin));
            var cellLocalScale = new Vector2(cellScale, cellScale);
            // NOTE: 無効値はスケールを変更しない
            if (float.IsNaN(cellLocalScale.x) || float.IsNaN(cellLocalScale.y))
            {
                return;
            }

            cellTransform.localScale = cellLocalScale;
        }

        void IGlowCustomCarouselViewDelegate.AccessoryButtonTappedForRowWith(
            GlowCustomInfiniteCarouselView carouselView,
            int indexPath,
            object identifier)
        {
            var stage = _viewModel.Stages[indexPath];
            //NOTE: EventQuestTopStageCell.Awakeでidentifierを設定
            if ((string)identifier == "info")
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
                _viewDelegate.OnStageInfoButtonTapped(stage.MstStageId);
            }
            else if ((string)identifier == "unRelease")
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_013);
                _viewDelegate.OnQuestUnReleasedClicked(stage.ReleaseRequireSentence);
            }
        }

        public bool CanMoveCarousel(CarouselDirection direction)
        {
            return _actualView.OnMoveButtonIfNeed(direction, NumberOfItems());
        }

        public void MoveLeft()
        {
            _actualView.MoveLeft();
        }

        public void MoveRight()
        {
            _actualView.MoveRight();
        }
    }
}
