using System;
using System.Collections;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.CustomCarousel;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Modules.Tutorial.Presentation.Views;
using GLOW.Scenes.QuestSelect.Presentation.QuestDifficultySelect;
using UIKit;
using UnityEngine;
using Wonderplanet.UIHaptics.Presentation;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using WPFramework.Presentation.Views;
using Zenject;

namespace GLOW.Scenes.QuestSelect.Presentation
{
    public class QuestSelectViewController : UIViewController<QuestSelectView>,
        IGlowCustomCarouselViewDelegate,
        IGlowCustomCarouselViewDataSource,
        IEscapeResponder
    {
        const string AccessoryButtonIdentifier = "select";

        public record Argument(Action StageSelected, MasterDataId InitialSelectedQuestId);

        [Inject] IQuestSelectViewDelegate ViewDelegate { get; }
        [Inject] IHapticsPresenter HapticsPresenter { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        [Inject] ITutorialBackKeyViewDelegate TutorialBackKeyHandler { get; }

        QuestSelectViewModel _questDataList;
        MasterDataId _initialSelectedMstQuestId;
        QuestSelectContentViewModel _selectingContentViewModel;

        bool _isQuestDecided;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            EscapeResponderRegistry.Bind(this, ActualView);

            //ちらつき防止のために透明にしておく
            ActualView.CanvasGroup.alpha = 0f;

            ActualView.CarouselView.DataSource = this;
            ActualView.CarouselView.ViewDelegate = this;
            ActualView.CarouselView.HapticsPresenter = HapticsPresenter;
            ActualView.CarouselView.CellDragHandler.ActionHandler.AddActionOnChangeCenterIndex(UpdateSideArrowButtons);

            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);

            ActualView.CanvasGroup.alpha = 1f;
            ActualView.FixContentLayout();
        }

        public void Initialize(QuestSelectViewModel viewModel)
        {
            _questDataList = viewModel;
            _selectingContentViewModel = viewModel.Items.ElementAtOrDefault(viewModel.CurrentIndex.Value)
                                         ?? QuestSelectContentViewModel.Empty;
            _initialSelectedMstQuestId = _selectingContentViewModel.GetCurrentDifficultyQuestId();

            ActualView.CarouselView.ReloadData();
            UpdateSideArrowButtons(_questDataList.CurrentIndex.Value);

            SetUpQuestContent(_selectingContentViewModel);
            SetUpDifficultyButtons(_selectingContentViewModel);
        }

        int IGlowCustomCarouselViewDataSource.NumberOfItems()
        {
            return _questDataList?.Items?.Count ?? 0;
        }

        GlowCustomInfiniteCarouselCell IGlowCustomCarouselViewDataSource.CellForItemAtIndex(int index)
        {
            var cell = ActualView.CarouselView.DequeueReusableCell<QuestSelectCell>();
            var model = _questDataList?.Items[index];
            if (model == null)
            {
                return cell;
            }

            cell.SymbolImage.AssetPath = model.AssetPath.Value;
            cell.DifficultyLabelComponent.gameObject.SetActive(model.OpenStatus == QuestOpenStatus.Released);
            cell.DifficultyLabelComponent.SetDifficulty(model.CurrentDifficulty);
            cell.NotReleaseObject.SetActive(model.OpenStatus != QuestOpenStatus.Released);
            cell.NewIconObj.SetActive(model.NewQuestExists);
            cell.SelectButton.interactable = model.OpenStatus == QuestOpenStatus.Released;
            cell.ReleaseRequireText.SetText(model.RequiredSentence.Value);
            cell.SetOnChangeCenterIndex(centerIndex => cell.OnUpdateButtonStatus(model.OpenStatus, centerIndex));
            cell.IsInitialSelected = model.QuestDifficultyItemViewModels.Any(m => m.MstQuestId == _initialSelectedMstQuestId);
            return cell;
        }

        int IGlowCustomCarouselViewDataSource.SelectedIndex()
        {
            return _questDataList?.CurrentIndex.Value ?? 0;
        }

        void IInfiniteCarouselViewDelegate.DidSelectItemAtIndex(int index)
        {
            _selectingContentViewModel = _questDataList?.Items.ElementAtOrDefault(index) ?? QuestSelectContentViewModel.Empty;

            SetUpQuestContent(_selectingContentViewModel);
            SetUpDifficultyButtons(_selectingContentViewModel);
        }

        void IGlowCustomCarouselViewDelegate.AccessoryButtonTappedForRowWith(
            GlowCustomInfiniteCarouselView carouselView, int indexPath, object identifier)
        {
            if ((string)identifier != AccessoryButtonIdentifier) return;
            if (_isQuestDecided) return;

            _isQuestDecided = true;

            var cell = carouselView.SelectedCell as QuestSelectCell;
            View.StartCoroutine(SelectQuestFromThumbnailTap(cell));
        }

        void IInfiniteCarouselViewDelegate.DidLayoutCell(InfiniteCarouselCell cell, int index)
        {
            // NOTE: 画面中央からの距離に応じてスケールを変更する
            var rectTransform = ActualView.CarouselView.RectTransform;
            var position = cell.RectTransform.localPosition;
            var distance = Mathf.Abs(position.x);
            var maxDistance = rectTransform.rect.width / ActualView.MaxDistanceMargin;
            distance = maxDistance < distance ? maxDistance : distance;

            var cellTransform = (RectTransform)cell.transform;
            var cellScale = maxDistance / (maxDistance + (distance * ActualView.CellSizeMargin));
            var cellLocalScale = new Vector2(cellScale, cellScale);
            // NOTE: 無効値はスケールを変更しない
            if (float.IsNaN(cellLocalScale.x) || float.IsNaN(cellLocalScale.y))
            {
                return;
            }

            cellTransform.localScale = cellLocalScale;
        }

        bool IEscapeResponder.OnEscape()
        {
            if (View.Hidden) return false;
            
            // チュートリアル中はバックキーを無効化
            if (TutorialBackKeyHandler.IsPlayingTutorial())
            {
                CommonToastWireFrame.ShowInvalidOperationMessage();
                return true;
            }
            UISoundEffector.Main.PlaySeEscape();
            OnBack();
            return true;
        }

        public void SelectDifficulty(Difficulty selectedDifficulty)
        {
            DifficultyButtonScaleAnimation(selectedDifficulty);

            var cell = ActualView.CarouselView.SelectedCell as QuestSelectCell;
            cell?.DifficultyLabelComponent.SetDifficulty(selectedDifficulty);

            ApplySelectStageDifficulty(selectedDifficulty);
        }

        void SetUpQuestContent(QuestSelectContentViewModel viewModel)
        {
            ActualView.QuestLockObject.SetActive(viewModel.OpenStatus != QuestOpenStatus.Released);
            ActualView.QuestName.SetText(viewModel.QuestName.Value);

            var flavorText = viewModel.OpenStatus == QuestOpenStatus.Released ? (string)viewModel.FlavorText.Value : "";
            ActualView.FlavorText.SetText(flavorText);
            // フレーバーテキスト設定後にスクロール位置をリセット
            ActualView.FlavorTextScrollRect.normalizedPosition = new Vector2(0, 1);
        }

        void SetUpDifficultyButtons(QuestSelectContentViewModel viewModel)
        {
            // Difficultyは貰った時点で開放されている難易度であることを保証している
            // すべて開放「されていない」とき、selectedDifficultyでNormalがくる
            var selectedDifficulty = viewModel.CurrentDifficulty;

            var normalViewModel = viewModel.QuestDifficultyItemViewModels.FirstOrDefault(
                model => model.Difficulty == Difficulty.Normal,
                QuestDifficultyItemViewModel.Empty);

            var hardViewModel = viewModel.QuestDifficultyItemViewModels.FirstOrDefault(
                model => model.Difficulty == Difficulty.Hard,
                QuestDifficultyItemViewModel.Empty);

            var extraViewModel = viewModel.QuestDifficultyItemViewModels.FirstOrDefault(
                model => model.Difficulty == Difficulty.Extra,
                QuestDifficultyItemViewModel.Empty);

            // 表示更新
            var shouldShowNormalButton = !normalViewModel.IsEmpty();
            ActualView.QuestDifficultyButtonListComponent.NormalButton.IsVisible = shouldShowNormalButton;

            if (shouldShowNormalButton)
            {
                ActualView.QuestDifficultyButtonListComponent.NormalButton.Setup(selectedDifficulty, normalViewModel);
            }

            var shouldShowHardButton = !hardViewModel.IsEmpty();
            ActualView.QuestDifficultyButtonListComponent.HardButton.IsVisible = shouldShowHardButton;

            if (shouldShowHardButton)
            {
                ActualView.QuestDifficultyButtonListComponent.HardButton.Setup(selectedDifficulty, hardViewModel);
            }

            var shouldShowExtraButton = !extraViewModel.IsEmpty();
            ActualView.QuestDifficultyButtonListComponent.ExtraButton.IsVisible = shouldShowExtraButton;

            if (shouldShowExtraButton)
            {
                ActualView.QuestDifficultyButtonListComponent.ExtraButton.Setup(selectedDifficulty, extraViewModel);
            }

            ActualView.QuestDifficultyButtonListComponent.UpdateButtonLayoutGroup();

            ActualView.SetUpCampaignBalloons(
                viewModel.NormalCampaignViewModels,
                viewModel.HardCampaignViewModels,
                viewModel.ExtraCampaignViewModels);
        }

        void UpdateSideArrowButtons(int centerIndex)
        {
            ActualView.LeftButton.gameObject.SetActive(0 < centerIndex);
            ActualView.RightButton.gameObject.SetActive(
                centerIndex < ActualView.CarouselView.DataSource.NumberOfItems() - 1);
        }

        void DifficultyButtonScaleAnimation(Difficulty selectDifficulty)
        {
            ActualView.QuestDifficultyButtonListComponent.NormalButton.PlayScaleAnimation(selectDifficulty);
            ActualView.QuestDifficultyButtonListComponent.HardButton.PlayScaleAnimation(selectDifficulty);
            ActualView.QuestDifficultyButtonListComponent.ExtraButton.PlayScaleAnimation(selectDifficulty);
        }

        IEnumerator SelectQuestFromThumbnailTap(QuestSelectCell cell)
        {
            View.UserInteraction = false;
            SoundEffectPlayer.Play(SoundEffectId.SSE_000_015);
            yield return cell?.StartSelectAnimation();
            View.UserInteraction = true;
            ViewDelegate.ApplySelectedQuest(_selectingContentViewModel.GetCurrentDifficultyQuestId());
        }

        void ApplySelectStageDifficulty(Difficulty selectDifficulty)
        {
            // Itemsの中で、初期状態から難易度が変更されたものをここで更新する
            var viewModel = _selectingContentViewModel.CopyWithUpdatedCurrentDifficulty(selectDifficulty);
            var updated = _questDataList.Items.Replace(_selectingContentViewModel, viewModel);
            _questDataList = _questDataList with { Items = updated };
            _selectingContentViewModel = viewModel;
        }

        void OnBack()
        {
            ViewDelegate.ApplySelectedQuest(_selectingContentViewModel.GetCurrentDifficultyQuestId());
        }

        [UIAction]
        void OnBackButtonSelected()
        {
            OnBack();
        }

        [UIAction]
        void OnRightButton()
        {
            ActualView.CarouselView.MoveRight();
        }

        [UIAction]
        void OnLeftButton()
        {
            ActualView.CarouselView.MoveLeft();
        }

        [UIAction]
        void OnNormalDifficultyButton()
        {
            ViewDelegate.OnDifficultySelected(_selectingContentViewModel.MstGroupQuestId, Difficulty.Normal);
        }

        [UIAction]
        void OnHardDifficultyButton()
        {
            ViewDelegate.OnDifficultySelected(_selectingContentViewModel.MstGroupQuestId, Difficulty.Hard);
        }

        [UIAction]
        void OnExtraDifficultyButton()
        {
            ViewDelegate.OnDifficultySelected(_selectingContentViewModel.MstGroupQuestId, Difficulty.Extra);
        }
    }
}
