import {state} from "../../../state.js";
import { renderKortyGrid } from "./courtGrid.js";
import { renderCourtDetail } from "./courtDetail.js";
export function renderKorty(){
  return state.selectedCourtId ? renderCourtDetail() : renderKortyGrid();
}