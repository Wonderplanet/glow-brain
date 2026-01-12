using GLOW.Core.Presentation.Components;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.EncyclopediaSeries.Presentation.Views
{
    public class EncyclopediaSeriesUnitListHeader : UICollectionViewSectionHeader
    {
        [SerializeField] GameObject _playerHeader;
        [SerializeField] UIText _playerHeaderText;
        [SerializeField] GameObject _enemyHeader;
        [SerializeField] UIText _enemyHeaderText;

        public void SetPlayerUnitHeader(int unlockCount, int max)
        {
            _playerHeader.SetActive(true);
            _enemyHeader.SetActive(false);

            _playerHeaderText.SetText($"{unlockCount}/{max}");
        }


        public void SetEnemyUnitHeader(int unlockCount, int max)
        {
            _playerHeader.SetActive(false);
            _enemyHeader.SetActive(true);

            _enemyHeaderText.SetText($"{unlockCount}/{max}");
        }
    }
}
