import {state} from "../../../state.js";
import { renderKortyGrid } from "./courtGrid.js";
import { renderCourtDetail } from "./courtDetail.js";
export async function renderKorty(){
  return state.selectedCourtId ? renderCourtDetail() : await renderKortyGrid();
}