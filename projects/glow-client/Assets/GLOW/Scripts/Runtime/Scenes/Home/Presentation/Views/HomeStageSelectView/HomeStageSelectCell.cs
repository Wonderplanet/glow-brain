using GLOW.Scenes.Home.Presentation.Components;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.Home.Presentation.Views
{
    public sealed class HomeStageSelectCell : UICollectionViewCell
    {
        [SerializeField] Text _nameText;
        [SerializeField] Text _descriptionText;
        [SerializeField] Text _scoreText;
        [SerializeField] HomeMainQuestSymbolImage _symbolImage;

        public string NameText { set => _nameText.text = value; }
        public string DescriptionText { set => _descriptionText.text = value; }
        public string ScoreText { set => _scoreText.text = value; }
        public HomeMainQuestSymbolImage HomeMainQuestSymbolImage => _symbolImage;
    }
}
