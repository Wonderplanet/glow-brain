using System;
using DG.Tweening;
using GLOW.Core.Domain.Modules.Time;
using GLOW.Core.Presentation.Modules;
using GLOW.Scenes.GameModeSelect.Domain;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.GameModeSelect.Presentation
{
    // UIKitを使わずHomeMainViewControllerを親にする。HomeMainViewControllerに依存性注入してもらう
    public class GameModeSelectViewController :  IUICollectionViewDelegate, IUICollectionViewDataSource
    {
        IGameModeSelectViewDelegate _viewDelegate;
        GameModeSelectView _actualView;
        GameModeSelectViewModel _viewModel = GameModeSelectViewModel.Empty;
        Action _onSelectGameMode;

        public void Initialize(GameModeSelectView actualView, IGameModeSelectViewDelegate viewDelegate, Action onSelectGameMode)
        {
            _actualView = actualView;
            _viewDelegate = viewDelegate;
            _onSelectGameMode = onSelectGameMode;
            _actualView.CollectionView.Delegate = this;
            _actualView.CollectionView.DataSource = this;
            _actualView.CloseButton.onClick.AddListener(Close);
            _actualView.BackgroundButton.onClick.AddListener(Close);
            _actualView.Hidden = true;
        }

        public void Show()
        {
            CancelTweenAnimation();
            _actualView.Hidden = false;
            var rect = (RectTransform)_actualView.RootObject.transform;
            rect.anchoredPosition = new Vector2(rect.anchoredPosition.x, _actualView.CloseStartY);
            rect.DOAnchorPos(new Vector2(0,_actualView.OpenStartY),0.3f)
                .SetEase(Ease.OutQuint)
                .Play();

            _viewDelegate.OnViewWillAppear(this);
        }
        public void Close()
        {
            CancelTweenAnimation();
            var rect = (RectTransform)_actualView.RootObject.transform;
            rect.anchoredPosition = new Vector2(rect.anchoredPosition.x, _actualView.OpenStartY);
            rect.DOAnchorPos(new Vector2(0,_actualView.CloseStartY),0.3f)
                .SetEase(Ease.OutQuint)
                .OnComplete(() => { _actualView.Hidden = true; })
                .Play();
        }

        void CancelTweenAnimation()
        {
            var rect = (RectTransform)_actualView.RootObject.transform;
            rect.DOComplete();
        }

        public bool Hidden()
        {
            return _actualView.Hidden;
        }

        public void SetViewModel(GameModeSelectViewModel viewModel)
        {
            _viewModel = viewModel;
            _actualView.CollectionView.ReloadData();
        }

        void IUICollectionViewDelegate.DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            //クエストカテゴリ選択処理
            var model = _viewModel.Items[indexPath.Row];
            _viewDelegate.OnGameModeButtonTap(model.Type,model.EventAssetKey, model.MstEventId, model.EndAt,_onSelectGameMode);
        }

        void IUICollectionViewDelegate.AccessoryButtonTappedForRowWith(UICollectionView collectionView, UIIndexPath indexPath, object identifier)
        {
            //no use.
        }

        int IUICollectionViewDataSource.NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _viewModel.IsEmpty() ? 0 : _viewModel.Items.Count;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var model = _viewModel.Items[indexPath.Row];
            var cell = collectionView.DequeueReusableCell<GameModeSelectCell>();

            if (model.Type == GameModeType.MeinQuest)
            {
                cell.EventModeImage.Image.sprite = cell.MainQuestSprite;
            }
            else
            {
                UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(
                    cell.EventModeImage.Image,
                    GameModeSelectAssetPath.ToButtonAssetPath(model.GameModeSelectAssetKey).Value);
            }

            cell.LimitTimeText.SetText(TimeSpanFormatter.Format(model.LimitTime));
            cell.TimeRoot.SetActive(model.ShowsLimitTime);
            cell.GameModeType = model.Type;

            cell.SelectedImage.Hidden = !model.IsSelected.Value;
            return cell;
        }
    }
}
