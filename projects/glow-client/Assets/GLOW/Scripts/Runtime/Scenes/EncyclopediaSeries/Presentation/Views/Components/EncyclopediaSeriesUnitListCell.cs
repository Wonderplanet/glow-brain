
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.EncyclopediaSeries.Domain.ValueObjects;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.EncyclopediaSeries.Presentation.Views
{
    public class EncyclopediaSeriesUnitListCell : UICollectionViewCell
    {
        [SerializeField] UIObject _heroIconObject;
        [SerializeField] UIObject _enemyIconObject;
        [SerializeField] CharacterIconComponent _characterIconComponent;
        [SerializeField] EnemySmallIconComponent _enemySmallIconComponent;
        [SerializeField] GameObject _disableObj;
        [SerializeField] UIObject _badge;

        public void Init()
        {
            _characterIconComponent.gameObject.SetActive(false);
            _enemySmallIconComponent.gameObject.SetActive(false);
            _disableObj.SetActive(false);
        }

        public void SetupCharacterIcon(
            CharacterIconViewModel viewModel,
            EncyclopediaUnlockFlag isUnlocked,
            NotificationBadge badge)
        {
            _badge.Hidden = !badge;

            if(!isUnlocked)
            {
                _disableObj.SetActive(true);
                return;
            }

            _characterIconComponent.SetupNoStatus(viewModel);
            _characterIconComponent.gameObject.SetActive(true);
            
            _heroIconObject.Hidden = false;
            _enemyIconObject.Hidden = true;
        }

        public void SetupEnemySmallIcon(
            EnemySmallIconViewModel viewModel,
            EncyclopediaUnlockFlag isUnlocked,
            NotificationBadge badge)
        {
            _badge.Hidden = !badge;

            if (!isUnlocked)
            {
                _disableObj.SetActive(true);
                return;
            }

            _enemySmallIconComponent.Setup(viewModel);
            _enemySmallIconComponent.gameObject.SetActive(true);
            
            _heroIconObject.Hidden = true;
            _enemyIconObject.Hidden = false;
        }

    }
}
