using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.EncyclopediaTop.Presentation.Views
{
    /// <summary>
    /// 91_図鑑
    /// 　91-1_図鑑
    /// 　　91-1-1_図鑑TOP画面
    /// </summary>
    public class EncyclopediaTopView : UIView
    {
        [SerializeField] UICollectionView _collectionView;
        [SerializeField] ChildScaler _childScaler;
        [SerializeField] UIText _unitTotalGrade;
        [SerializeField] GameObject _bonusBadge;

        public UICollectionView CollectionView => _collectionView;

        public bool HiddenBonusBadge
        {
            set => _bonusBadge.SetActive(!value);
        }

        public void InitializeView()
        {
            _unitTotalGrade.gameObject.SetActive(false);
            _bonusBadge.SetActive(false);
        }

        public void Setup(UnitGrade grade, NotificationBadge badge)
        {
            _unitTotalGrade.gameObject.SetActive(true);
            _unitTotalGrade.SetText(grade.Value.ToString());
            _bonusBadge.SetActive(badge.Value);
        }

        public void PlayCellAppearanceAnimation()
        {
            _childScaler.Play();
        }
    }
}
