namespace GLOW.Scenes.InGame.Domain.Battle.ScoreCalculator
{
    public enum ScoreCalculateType
    {
        None,
        AllEnemyUnitsAndOutPost,//AllEnemyUnitsとEnemyOutpostで表現できそうだけどいったん置く
        AllEnemyUnits,
        BossEnemyUnits,
        EnemyOutpost
    }
}
