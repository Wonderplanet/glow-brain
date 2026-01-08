using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.EncyclopediaSeries.Domain.ValueObjects;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.EncyclopediaSeries.Presentation.Views
{
    public class ProvisionRatioLineupListCell : UICollectionViewCell
    {
        [SerializeField] CharacterIconComponent _characterIconComponent;
        [SerializeField] EnemySmallIconComponent _enemySmallIconComponent;
        [SerializeField] Button _button;

        public void SetupCharacterIcon(CharacterIconViewModel viewModel, EncyclopediaUnlockFlag isUnlocked)
        {
            _characterIconComponent.Setup(viewModel);
            _characterIconComponent.gameObject.SetActive(true);
            _enemySmallIconComponent.gameObject.SetActive(false);
            _button.interactable = isUnlocked.Value;
        }

        public void SetupEnemySmallIcon(EnemySmallIconViewModel viewModel, EncyclopediaUnlockFlag isUnlocked)
        {
            _enemySmallIconComponent.Setup(viewModel);
            _enemySmallIconComponent.gameObject.SetActive(true);
            _characterIconComponent.gameObject.SetActive(false);
            _button.interactable = isUnlocked.Value;
        }
    }
}
