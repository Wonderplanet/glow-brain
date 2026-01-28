using GLOW.Core.Presentation.Components;
using GLOW.Scenes.StaminaRecover.Domain.ValueObject;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.StaminaRecover.Presentation.StaminaRecoverySelect
{
    public class StaminaRecoverySelectView : UIView
    {
        [SerializeField] UICollectionView _collectionView;
        [SerializeField] GameObject _dialogShortageText;

        public void Initialize(
            IUICollectionViewDelegate collectionViewDelegate,
            IUICollectionViewDataSource collectionViewDataSource)
        {
            _collectionView.Delegate = collectionViewDelegate;
            _collectionView.DataSource = collectionViewDataSource;
        }

        public void ReloadData()
        {
            _collectionView.ReloadData();
        }

        public void SetUpDialogText(StaminaShortageFlag isStaminaShortage)
        {
            _dialogShortageText.SetActive(isStaminaShortage.IsStaminaShortage);
        }
    }
}
