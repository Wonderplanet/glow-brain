using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.QuestSelect.Presentation.QuestDifficultySelect.Component
{
    public class QuestDifficultyButtonSpriteComponent : UIObject
    {
        [SerializeField] UIImage _sprite;
        [SerializeField] Sprite[] _buttonSprites = {};

        public void Setup(Difficulty difficulty)
        {
            _sprite.Sprite = GetSprite(difficulty);
            _sprite.Hidden = false;
        }

        Sprite GetSprite(Difficulty difficulty)
        {
            var index = (int)difficulty;

            return index >= _buttonSprites.Length ? null : _buttonSprites[index];
        }
    }
}