namespace GLOW.Scenes.InGame.Domain.Battle.CharacterUnitAction
{
    public static class UnitMoveActionFactory
    {
        public static ICharacterUnitAction Create(bool isMoveStarted)
        {
            if (isMoveStarted)
            {
                return new CharacterUnitMoveAction();
            }

            return new CharacterUnitPrevMoveAction();
        }
    }
}
