using System.Threading;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Domain.Modules.Time;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.Calculator;
using GLOW.Core.Presentation.Components;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.ComebackDailyBonus.Presentation.View
{
    public class ComebackDailyBonusView : UIView
    {
        [SerializeField] UICollectionView _collectionView;
        [SerializeField] UIImage _comebackBonusBannerImage;
        [SerializeField] UIText _comebackBonusRemainingTimeText;
        [SerializeField] Button _closeButton;

        public void InitializeCollectionView(
            IUICollectionViewDataSource dataSource,
            IUICollectionViewDelegate viewDelegate)
        {
            _collectionView.DataSource = dataSource;
            _collectionView.Delegate = viewDelegate;
        }
        
        public void ReloadCollectionView()
        {
            _collectionView.ReloadData();
        }

        public UICollectionViewCell GetCollectionViewCellFromLoginDayCount(LoginDayCount loginDayCount)
        {
            return _collectionView.CellForRow(new UIIndexPath(0, loginDayCount.Value - 1));
        }
        
        public async UniTask MoveScrollToCell(DailyBonusCollectionCellComponent collectionCellComponent, CancellationToken cancellationToken)
        {
            var rectTransform = collectionCellComponent.transform as RectTransform;
            if (rectTransform == null)
                return;

            var normalizedPos = ScrollPositionCalculator.CalculateTargetPositionInScroll(_collectionView.ScrollRect, rectTransform);
            await _collectionView.ScrollRect.DOVerticalNormalizedPos(normalizedPos, 0.5f).SetEase(Ease.InOutExpo).WithCancellation(cancellationToken);
        }
        
        public async UniTask MoveScrollToBottom(CancellationToken cancellationToken)
        {
            await _collectionView.ScrollRect.DOVerticalNormalizedPos(0.0f, 0.5f)
                .SetEase(Ease.InOutExpo)
                .WithCancellation(cancellationToken);
        }

        public void SetRemainingTime(RemainingTimeSpan remainingTimeSpan)
        {
            _comebackBonusRemainingTimeText.SetText(TimeSpanFormatter.FormatUntilEnd(remainingTimeSpan));
        }
        
        public void SetCloseButtonInteractable(bool interactable)
        {
            _closeButton.interactable = interactable;
        }
    }
}