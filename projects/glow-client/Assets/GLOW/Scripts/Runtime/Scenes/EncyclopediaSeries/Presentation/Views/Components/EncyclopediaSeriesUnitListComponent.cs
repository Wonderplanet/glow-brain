using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.EncyclopediaSeries.Domain.ValueObjects;
using GLOW.Scenes.EncyclopediaSeries.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.EncyclopediaSeries.Presentation.Views
{
    public class EncyclopediaSeriesUnitListComponent : UIComponent,
        IUICollectionViewDelegate,
        IUICollectionViewDataSource
    {
        [SerializeField] UICollectionView _collectionView;
        [SerializeField] ChildScaler _childScaler;

        const int PlayerUnitSection = 0;
        const int EnemyUnitSection = 1;
        const int MaxSection = 2;

        IReadOnlyList<EncyclopediaPlayerUnitListCellViewModel> _playerUnitViewModels = new List<EncyclopediaPlayerUnitListCellViewModel>();
        IReadOnlyList<EncyclopediaEnemyUnitListCellViewModel> _enemyUnitViewModels = new List<EncyclopediaEnemyUnitListCellViewModel>();

        Action<MasterDataId, EncyclopediaUnlockFlag> _onSelectPlayerUnitAction;
        Action<MasterDataId, EncyclopediaUnlockFlag> _onSelectEnemyUnitAction;

        protected override void Awake()
        {
            base.Awake();
            _collectionView.Delegate = this;
            _collectionView.DataSource = this;
        }

        public void Setup(
            IReadOnlyList<EncyclopediaPlayerUnitListCellViewModel> playerUnitViewModels,
            IReadOnlyList<EncyclopediaEnemyUnitListCellViewModel> enemyUnitViewModels,
            Action<MasterDataId, EncyclopediaUnlockFlag> onSelectPlayerUnitAction,
            Action<MasterDataId, EncyclopediaUnlockFlag> onSelectEnemyUnitAction)
        {
            _onSelectPlayerUnitAction = onSelectPlayerUnitAction;
            _onSelectEnemyUnitAction = onSelectEnemyUnitAction;
            _playerUnitViewModels = playerUnitViewModels;
            _enemyUnitViewModels = enemyUnitViewModels;

            _collectionView.ReloadData();
        }
        
        public void PlayCellAppearanceAnimation()
        {
            _childScaler.Play();
        }

        void IUICollectionViewDelegate.DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            if (IsPlayerUnitSection(indexPath.Section))
            {
                var playerUnitViewModel = _playerUnitViewModels[indexPath.Row];
                _onSelectPlayerUnitAction?.Invoke(playerUnitViewModel.MstUnitId, playerUnitViewModel.IsUnlocked);
            }
            else
            {
                var enemyUnitViewModel = _enemyUnitViewModels[indexPath.Row];
                _onSelectEnemyUnitAction?.Invoke(enemyUnitViewModel.MstEnemyId, enemyUnitViewModel.IsUnlocked);
            }
        }

        void IUICollectionViewDelegate.AccessoryButtonTappedForRowWith(UICollectionView collectionView, UIIndexPath indexPath, object identifier)
        {
        }

        int IUICollectionViewDataSource.NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            if(IsPlayerUnitSection(section)) return _playerUnitViewModels?.Count ?? 0;
            return _enemyUnitViewModels?.Count ?? 0;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var cell = collectionView.DequeueReusableCell<EncyclopediaSeriesUnitListCell>();
            cell.Init();

            if (IsPlayerUnitSection(indexPath.Section))
            {
                var playerUnitViewModel = _playerUnitViewModels[indexPath.Row];
                cell.SetupCharacterIcon(
                    playerUnitViewModel.Icon,
                    playerUnitViewModel.IsUnlocked,
                    playerUnitViewModel.NewBadge);
            }
            else
            {
                var enemyUnitViewModel = _enemyUnitViewModels[indexPath.Row];
                cell.SetupEnemySmallIcon(
                    enemyUnitViewModel.Icon,
                    enemyUnitViewModel.IsUnlocked,
                    enemyUnitViewModel.NewBadge);
            }
            return cell;
        }

        // セクション数のチェック
        int IUICollectionViewDataSource.NumberOfSection()
        {
            var player = _playerUnitViewModels.Count > 0 ? 1 : 0;
            var enemy = _enemyUnitViewModels.Count > 0 ? 1 : 0;
            return player + enemy;
        }

        // 各セクションでヘッダーを使用するかのチェック
        bool IUICollectionViewDataSource.IsUseSectionHeaderOfSectionIndex(int section)
        {
            if(IsPlayerUnitSection(section))
            {
                return _playerUnitViewModels.Count > 0;
            }
            return _enemyUnitViewModels.Count > 0;
        }

        // 各セクションで使用するヘッダーオブジェクトの生成
        UICollectionViewSectionHeader IUICollectionViewDataSource.SectionHeaderOfSectionIndex(
            UICollectionView collectionView, int section)
        {
            var header = collectionView.DequeueReusableHeader<EncyclopediaSeriesUnitListHeader>();
            if (IsPlayerUnitSection(section))
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

        bool IsPlayerUnitSection(int section)
        {
            return _playerUnitViewModels.Count > 0 && section == PlayerUnitSection;
        }
    }
}
