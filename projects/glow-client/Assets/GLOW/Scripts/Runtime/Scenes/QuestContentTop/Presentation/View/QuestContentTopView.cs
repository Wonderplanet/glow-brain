using GLOW.Scenes.QuestContentTop.Domain.enums;
using UIKit;
using UnityEngine;
using Zenject;

namespace GLOW.Scenes.QuestContentTop.Presentation
{
    public class QuestContentTopView : UIView
    {
        [SerializeField] UICollectionView _collectionView;
        
        [Inject] IQuestContentTopViewControllerListener ViewControllerListener { get; }

        public void Initialize(IUICollectionViewDelegate collectionViewDelegate, IUICollectionViewDataSource collectionViewDataSource)
        {
            _collectionView.Delegate = collectionViewDelegate;
            _collectionView.DataSource = collectionViewDataSource;
        }
        
        public void RefreshCollectionView()
        {
            _collectionView.ReloadData();
        }
        
        public UICollectionViewCell GetCollectionViewCell(int section, int row)
        {
            var indexPath = new UIIndexPath(section, row);
            return _collectionView.CellForRow(indexPath);
        }
        
        public void ScrollToRowInSection(int section, int row)
        {
            var indexPath = new UIIndexPath(section, row);
            _collectionView.ScrollToRowAt(indexPath, UICollectionView.ScrollPosition.Middle, false);
        }
        
        public void SetEnableScroll(bool enable)
        {
            _collectionView.ScrollRect.enabled = enable;
        }

        public void ScrollToContentCell(QuestContentTopElementType type)
        {
            ViewControllerListener.ScrollToContentCell(type);
        }
    }
}
