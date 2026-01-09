using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.AdventBattle.Presentation.Component
{
    public class AdventBattleHighScorePlateComponent : UIObject
    {
        [SerializeField] UIText _highScoreText;
        [SerializeField] UIImage _plateImage;
        [SerializeField] Sprite _defaultPlateSprites;
        [SerializeField] Sprite _pickupPlateSprites;
        [SerializeField] Color _defaultTextColor;
        [SerializeField] Color _pickupTextColor;

        public void SetupHighScorePlate(AdventBattleScore score, bool isPickup)
        {
            _highScoreText.SetText(score.ToDisplayString());
            _plateImage.Sprite = isPickup ? _pickupPlateSprites : _defaultPlateSprites;
            _highScoreText.SetColor(isPickup ? _pickupTextColor : _defaultTextColor);
        }
    }
}