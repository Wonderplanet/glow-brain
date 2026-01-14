using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.EncyclopediaSeries.Presentation.ViewModels;
using GLOW.Scenes.EncyclopediaSeries.Presentation.Views;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.FragmentProvisionRatio.Presentation.ProvisionRatioLineupComponent
{
    public class ProvisionRatioLineupListComponent : UIComponent,
        IUICollectionViewDelegate,
        IUICollectionViewDataSource
    {
        [SerializeField] UICollectionView _collectionView;

        const int MaxSection = 2;
        const int PlayerUnitSection = 0;
        const int EnemyUnitSection = 1;

        IReadOnlyList<EncyclopediaPlayerUnitListCellViewModel> _playerUnitViewModels;
        IReadOnlyList<EncyclopediaEnemyUnitListCellViewModel> _enemyUnitViewModels;

        Action<MasterDataId> _onSelectPlayerUnitAction;
        Action<MasterDataId> _onSelectEnemyUnitAction;

        public void Init()
        {
            _collectionView.Delegate = this;
            _collectionView.DataSource = this;
        }

        public void Setup(
            IReadOnlyList<EncyclopediaPlayerUnitListCellViewModel> playerUnitViewModels,
            IReadOnlyList<EncyclopediaEnemyUnitListCellViewModel> enemyUnitViewModels,
            Action<MasterDataId> onSelectPlayerUnitAction,
            Action<MasterDataId> onSelectEnemyUnitAction)
        {
            _onSelectPlayerUnitAction = onSelectPlayerUnitAction;
            _onSelectEnemyUnitAction = onSelectEnemyUnitAction;
            _playerUnitViewModels = playerUnitViewModels;
            _enemyUnitViewModels = enemyUnitViewModels;

            _collectionView.ReloadData();
        }

        void IUICollectionViewDelegate.DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            if (indexPath.Section == PlayerUnitSection)
            {
                var playerUnitViewModel = _playerUnitViewModels[indexPath.Row];
                _onSelectPlayerUnitAction?.Invoke(playerUnitViewModel.MstUnitId);
            }
            else
            {
                var enemyUnitViewModel = _enemyUnitViewModels[indexPath.Row];
                _onSelectEnemyUnitAction?.Invoke(enemyUnitViewModel.MstEnemyId);
            }
        }

        void IUICollectionViewDelegate.AccessoryButtonTappedForRowWith(UICollectionView collectionView, UIIndexPath indexPath, object identifier)
        {
        }

        int IUICollectionViewDataSource.NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            if(section == PlayerUnitSection) return _playerUnitViewModels?.Count ?? 0;
            return _enemyUnitViewModels?.Count ?? 0;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var cell = collectionView.DequeueReusableCell<EncyclopediaSeriesUnitListCell>();
            cell.Init();
            if (indexPath.Section == PlayerUnitSection)
            {
                var playerUnitViewModel = _playerUnitViewModels[indexPath.Row];
                cell.SetupCharacterIcon(playerUnitViewModel.Icon, playerUnitViewModel.IsUnlocked, NotificationBadge.False);
            }
            else
            {
                var enemyUnitViewModel = _enemyUnitViewModels[indexPath.Row];
                cell.SetupEnemySmallIcon(enemyUnitViewModel.Icon, enemyUnitViewModel.IsUnlocked, NotificationBadge.False);
            }
            return cell;
        }

        // NOTE: v0.4.0 で追加
        int IUICollectionViewDataSource.NumberOfSection() => _enemyUnitViewModels != null ? MaxSection : 0;
        bool IUICollectionViewDataSource.IsUseSectionHeaderOfSectionIndex(int section)
        {
            return true;
        }

        UICollectionViewSectionHeader IUICollectionViewDataSource.SectionHeaderOfSectionIndex(
            UICollectionView collectionView, int section)
        {
            var header = collectionView.DequeueReusableHeader<EncyclopediaSeriesUnitListHeader>();
            if (section == PlayerUnitSection)
            {
                var playerUnlockCount = _playerUnitViewModels.Count(vm => vm.IsUnlocked.Value);
                header.SetPlayerUnitHeader(playerUnlockCount, _playerUnitViewModels.Count);
            }
            else
            {
                var enemyUnlockCount = _enemyUnitViewModels.Count == 0 ? 0 : _enemyUnitViewModels.Count(vm => vm.IsUnlocked.Value);
                header.SetEnemyUnitHeader(enemyUnlockCount, _enemyUnitViewModels.Count);
            }
            return header;
        }
    }
}
