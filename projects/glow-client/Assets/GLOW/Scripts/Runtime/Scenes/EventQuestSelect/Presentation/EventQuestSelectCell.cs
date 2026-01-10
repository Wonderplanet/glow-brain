using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Core.Presentation.Components;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.EventQuestSelect.Presentation
{
    public class EventQuestSelectCell : UICollectionViewCell
    {
        [SerializeField] UIText _name;
        [SerializeField] UIImage _thumbnail;
        [Header("グレーアウト")]
        [SerializeField] GameObject _lockObject;
        [SerializeField] GameObject _lockIcon;
        [SerializeField] UIText _lockRequireStageText;
        [Header("New/Clearアイコン")]
        [SerializeField] GameObject _newObject;


        public void SetUpCell(EventQuestSelectElementViewModel viewModel)
        {
            _name.SetText(viewModel.Name.Value);
            _lockObject.SetActive(!viewModel.IsOpen());

            _lockIcon.SetActive(viewModel.ShouldShowLockIcon());

            var lockDescription = viewModel.GetLockDescription();
            _lockRequireStageText.gameObject.SetActive(!string.IsNullOrEmpty(lockDescription));
            _lockRequireStageText.SetText(lockDescription);

            UISpriteUtil.LoadSpriteWithFade(_thumbnail.Image, viewModel.AssetPath.Value);
            _newObject.SetActive(viewModel.IsNewQuest);
        }
    }
}
