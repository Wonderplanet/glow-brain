using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    /// <summary>
    /// 覚醒（Awake）、ランクアップ(RankUP)、レベルアップ（LevelUP）の素材が足りない場合の例外
    /// </summary>
    public class ItemAmountIsNotEnoughException : DataInconsistencyServerErrorException
    {
        public ItemAmountIsNotEnoughException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
