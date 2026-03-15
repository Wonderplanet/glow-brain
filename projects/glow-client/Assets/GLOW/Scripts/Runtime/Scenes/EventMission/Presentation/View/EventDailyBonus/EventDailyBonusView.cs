using System.Threading;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Presentation.Calculator;
using GLOW.Core.Presentation.Components;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.EventMission.Presentation.View.EventDailyBonus
{
    /// <summary>
    /// 61_ミッション
    /// 　61-1-3_ログインボーナス
    /// </summary>
    public class EventDailyBonusView : UIView
    {
        [SerializeField] UICollectionView _collectionView;

        public UICollectionView CollectionView => _collectionView;

        public async UniTask MoveScrollToCell(DailyBonusCollectionCellComponent collectionCellComponent, CancellationToken cancellationToken)
        {
            var rectTransform = collectionCellComponent.transform as RectTransform;
            if (rectTransform == null)
                return;

            var normalizedPos = ScrollPositionCalculator.CalculateTargetPositionInScroll(_collectionView.ScrollRect, rectTransform);
            await _collectionView.ScrollRect.DOVerticalNormalizedPos(normalizedPos, 0.5f).SetEase(Ease.InOutExpo).WithCancellation(cancellationToken);
        }
    }
}
