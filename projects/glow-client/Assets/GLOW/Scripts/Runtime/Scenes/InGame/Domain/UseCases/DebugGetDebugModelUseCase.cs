#if GLOW_INGAME_DEBUG
using System.Collections.Generic;
using GLOW.Debugs.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;
using System.Linq;
using GLOW.Core.Domain.Models;
using GLOW.Scenes.InGame.Domain.Constants;

namespace GLOW.Scenes.InGame.Domain.UseCases
{
    public class DebugGetDebugModelUseCase
    {
        [Inject] IInGameScene InGameScene { get; }
        [Inject] IMstCharacterDataRepository CharacterDataRepository { get; }
        [Inject] IMstEnemyCharacterDataRepository MstEnemyCharacterDataRepository { get; }

        public InGameDebugModel GetDebugModel()
        {
            var debugModel = InGameScene.Debug;

            return debugModel with {
                FieldUnitInfos = CreateFieldUnitInfos(),
                IsPlayerOutpostDamageInvalidation = InGameScene.PlayerOutpost.DamageInvalidationFlag,
                IsEnemyOutpostDamageInvalidation = InGameScene.EnemyOutpost.DamageInvalidationFlag,
                OutpostEnhancement = InGameScene.OutpostEnhancement
            };
        }

        List<DebugFieldUnitInfoModel> CreateFieldUnitInfos()
        {
            return InGameScene.CharacterUnits
                .Select(unit =>
                {
                    var name = GetUnitName(unit);
                    var status = new DebugUnitStatusModel(unit.MaxHp, unit.Hp, unit.AttackPower);

                    return new DebugFieldUnitInfoModel(
                        unit.Id,
                        name,
                        unit.Kind,
                        status
                    );
                })
                .ToList();
        }

        CharacterName GetUnitName(CharacterUnitModel unit)
        {
            if (InGameScene.Type == InGameType.Pvp)
            {
                var mstCharacter = CharacterDataRepository.GetCharacter(unit.CharacterId);
                return mstCharacter.Name;
            }

            if (unit.BattleSide == BattleSide.Enemy)
            {
                var enemyModel = MstEnemyCharacterDataRepository.GetEnemyCharacter(unit.CharacterId);
                return enemyModel.Name;
            }
            else
            {
                var mstCharacter = CharacterDataRepository.GetCharacter(unit.CharacterId);
                return mstCharacter.Name;
            }
        }
    }
}
#endif //GLOW_INGAME_DEBUG
