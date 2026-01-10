using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.EncyclopediaSeries.Domain.Models;
using GLOW.Scenes.EncyclopediaSeries.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.EncyclopediaSeries.Domain.UseCases
{
    public class GetEncyclopediaSeriesUnitListUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IMstEnemyCharacterDataRepository MstEnemyCharacterDataRepository { get; }

        public EncyclopediaSeriesUnitListModel GetUnitList(MasterDataId mstSeriesId)
        {
            var gameFetchOther = GameRepository.GetGameFetchOther();

            var mstUnits = MstCharacterDataRepository.GetSeriesCharacters(mstSeriesId);
            var playerUnits = mstUnits
                .Select(mstUnit => TranslatePlayerUnitModel(gameFetchOther, mstUnit))
                .OrderByDescending(model => model.IsUnlocked)
                .ToList();

            var mstEnemies = MstEnemyCharacterDataRepository.GetSeriesEnemyCharacters(mstSeriesId);
            var enemyUnits = mstEnemies
                .Where(mstEnemy => mstEnemy.VisibleOnEncyclopediaFlag)
                .Select(mstEnemy => TranslateEnemyModel(mstEnemy, gameFetchOther.UserEnemyDiscoverModels))
                .OrderByDescending(model => model.IsUnlocked)
                .ToList();

            return new EncyclopediaSeriesUnitListModel(
                playerUnits,
                enemyUnits);
        }

        EncyclopediaPlayerUnitListCellModel TranslatePlayerUnitModel(
            GameFetchOtherModel gameFetchOther,
            MstCharacterModel mstUnit)
        {
            var iconModel = CharacterIconModelFactory.Create(mstUnit);
            var userUnit =
                gameFetchOther.UserUnitModels.FirstOrDefault(unit => unit.MstUnitId == mstUnit.Id, UserUnitModel.Empty);
            var isUnlocked = !userUnit.IsEmpty();
            var isNew = new NotificationBadge(userUnit.IsNewEncyclopedia);

            return new EncyclopediaPlayerUnitListCellModel(
                mstUnit.Id,
                iconModel,
                new EncyclopediaUnlockFlag(isUnlocked),
                isNew);
        }

        EncyclopediaSeriesEnemyListCellModel TranslateEnemyModel(
            MstEnemyCharacterModel mstEnemy,
            IReadOnlyList<UserEnemyDiscoverModel> enemyDiscoverModels)
        {
            var iconModel = EnemyIconModelFactory.CreateSmallIcon(mstEnemy);
            var userEnemyDiscover =
                enemyDiscoverModels.FirstOrDefault(enemyDiscover => enemyDiscover.MstEnemyCharacterId == mstEnemy.Id,
                    UserEnemyDiscoverModel.Empty);
            var isUnlocked = !userEnemyDiscover.IsEmpty();
            var isNew = new NotificationBadge(userEnemyDiscover.IsNewEncyclopedia);

            return new EncyclopediaSeriesEnemyListCellModel(
                mstEnemy.Id,
                iconModel,
                new EncyclopediaUnlockFlag(isUnlocked),
                isNew);
        }
    }
}
